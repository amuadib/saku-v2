<?php

use App\Http\Middleware\OtomatisPotongAdminTabungan;
use App\Jobs\ProsesAdminTabungan;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::flush();
});

it('tidak memicu job jika pengguna belum login atau bukan petugas', function () {
    Queue::fake();

    $request = Request::create('/admin', 'GET');
    $middleware = new OtomatisPotongAdminTabungan;

    $response = $middleware->handle($request, function () {
        return new Response('OK', 200);
    });

    expect($response->getStatusCode())->toEqual(200);
    Queue::assertNothingPushed();
});

it('memicu job jika admin mengakses aplikasi dan tahun ini belum diproses', function () {
    Queue::fake();

    $tahun = (int) date('Y');
    $user = User::factory()->create([
        'role_id' => 1, // Admin
    ]);

    $request = Request::create('/admin', 'GET');
    $request->setUserResolver(fn () => $user);

    $middleware = new OtomatisPotongAdminTabungan;
    $response = $middleware->handle($request, function () {
        return new Response('OK', 200);
    });

    app()->terminate();

    expect($response->getStatusCode())->toEqual(200);
    expect(Cache::has('tabungan_admin_checked_'.$tahun))->toBeTrue();

    Queue::assertPushed(ProsesAdminTabungan::class, function ($job) use ($user, $tahun) {
        return $job->user_id === (string) $user->id && $job->tahun === $tahun;
    });
});

it('tidak memicu job jika tahun ini sudah pernah diproses di database', function () {
    Queue::fake();

    $tahun = (int) date('Y');
    Setting::create([
        'key' => 'tabungan_admin_'.$tahun,
        'payload' => ['tahun' => $tahun],
    ]);

    $user = User::factory()->create([
        'role_id' => 1,
    ]);

    $request = Request::create('/admin', 'GET');
    $request->setUserResolver(fn () => $user);

    $middleware = new OtomatisPotongAdminTabungan;
    $response = $middleware->handle($request, function () {
        return new Response('OK', 200);
    });

    app()->terminate();

    expect($response->getStatusCode())->toEqual(200);
    expect(Cache::has('tabungan_admin_checked_'.$tahun))->toBeTrue();
    Queue::assertNothingPushed();
});

it('segera mengembalikan respon dari cache tanpa query jika cache sudah ditandai', function () {
    Queue::fake();

    $tahun = (int) date('Y');
    Cache::put('tabungan_admin_checked_'.$tahun, true, 3600);

    $user = User::factory()->create([
        'role_id' => 1,
    ]);

    $request = Request::create('/admin', 'GET');
    $request->setUserResolver(fn () => $user);

    $middleware = new OtomatisPotongAdminTabungan;
    $response = $middleware->handle($request, function () {
        return new Response('OK', 200);
    });

    expect($response->getStatusCode())->toEqual(200);
    Queue::assertNothingPushed();
});

it('terintegrasi secara end-to-end melalui http request admin', function () {
    Queue::fake();

    $tahun = (int) date('Y');
    $user = User::factory()->create([
        'role_id' => 1,
    ]);

    $this->actingAs($user)->get(route('admin.dashboard'));

    expect(Cache::has('tabungan_admin_checked_'.$tahun))->toBeTrue();
    Queue::assertPushed(ProsesAdminTabungan::class);
});
