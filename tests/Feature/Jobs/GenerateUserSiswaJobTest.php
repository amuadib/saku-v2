<?php

use App\Jobs\GenerateUserSiswaJob;
use App\Livewire\Admin\Siswa\SiswaList;
use App\Models\Siswa;
use App\Models\User;
use App\Notifications\UserSiswaGeneratedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::flush();

    $this->admin = User::factory()->create([
        'role_id' => 1,
    ]);

    $this->actingAs($this->admin);
});

it('membuat akun user untuk siswa aktif yang belum memiliki user', function () {
    Notification::fake();

    $siswa1 = Siswa::create([
        'nama' => 'Ahmad Fulan',
        'nisn' => '0012345678',
        'status' => 1,
        'lembaga_id' => 1,
        'jenis_kelamin' => 'l',
    ]);

    $siswa2 = Siswa::create([
        'nama' => 'Budi Santoso',
        'nis' => '102030',
        'status' => 1,
        'lembaga_id' => 1,
        'jenis_kelamin' => 'l',
    ]);

    $siswa3 = Siswa::create([
        'nama' => 'Citra Lestari',
        'status' => 1,
        'lembaga_id' => 1,
        'jenis_kelamin' => 'p',
    ]);

    $job = new GenerateUserSiswaJob(userId: $this->admin->id);
    $job->handle();

    // 1. Cek User untuk Siswa 1 (username = NISN)
    $user1 = User::where('authable_type', Siswa::class)->where('authable_id', $siswa1->id)->first();
    expect($user1)->not->toBeNull();
    expect($user1->username)->toEqual('0012345678');
    expect($user1->role_id)->toEqual(5);
    expect(Hash::check('0012345678', $user1->password))->toBeTrue();

    // 2. Cek User untuk Siswa 2 (username = NIS)
    $user2 = User::where('authable_type', Siswa::class)->where('authable_id', $siswa2->id)->first();
    expect($user2)->not->toBeNull();
    expect($user2->username)->toEqual('102030');
    expect($user2->role_id)->toEqual(5);

    // 3. Cek User untuk Siswa 3 (username = nama 5 huruf + angka)
    $user3 = User::where('authable_type', Siswa::class)->where('authable_id', $siswa3->id)->first();
    expect($user3)->not->toBeNull();
    expect($user3->username)->toStartWith('citra');
    expect($user3->role_id)->toEqual(5);

    // 4. Cek cache status
    $status = Cache::get('generate_user_siswa_status_'.$this->admin->id);
    expect($status)->not->toBeNull();
    expect($status['count'])->toEqual(3);

    // 5. Cek notifikasi database
    Notification::assertSentTo($this->admin, UserSiswaGeneratedNotification::class, function ($notification) {
        return $notification->count === 3;
    });
});

it('melewati siswa yang sudah memiliki akun user', function () {
    Notification::fake();

    $siswa = Siswa::create([
        'nama' => 'Doni Perkasa',
        'nis' => '998877',
        'status' => 1,
        'lembaga_id' => 1,
        'jenis_kelamin' => 'l',
    ]);

    // Sudah punya user
    User::create([
        'role_id' => 5,
        'username' => 'existing_user',
        'password' => Hash::make('secret'),
        'authable_type' => Siswa::class,
        'authable_id' => $siswa->id,
    ]);

    $job = new GenerateUserSiswaJob(userId: $this->admin->id);
    $job->handle();

    // Jumlah user tetap 1 untuk siswa ini
    expect(User::where('authable_type', Siswa::class)->where('authable_id', $siswa->id)->count())->toEqual(1);

    $status = Cache::get('generate_user_siswa_status_'.$this->admin->id);
    expect($status['count'])->toEqual(0);
});

it('dapat mendispatch job generate user dari komponen SiswaList dan mendeteksi selesainya', function () {
    Queue::fake();

    $component = Livewire::test(SiswaList::class)
        ->call('generateUserSiswa');

    Queue::assertPushed(GenerateUserSiswaJob::class, function ($job) {
        return $job->userId === (string) $this->admin->id;
    });

    $component->assertSet('isGeneratingUser', true);

    // Simulasikan job telah selesai dan menyimpan status ke cache
    Cache::put('generate_user_siswa_status_'.$this->admin->id, [
        'count' => 5,
        'total' => 10,
        'timestamp' => now()->timestamp,
    ]);

    // Panggil pengecekan status (seperti yang dilakukan polling)
    $component->call('checkGenerateUserStatus')
        ->assertSet('isGeneratingUser', false);

    expect(Cache::has('generate_user_siswa_status_'.$this->admin->id))->toBeFalse();
});
