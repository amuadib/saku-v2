<?php

use App\Livewire\Admin\User\UserList;
use App\Models\Anggota;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->adminAnggota = Anggota::create([
        'nama' => 'Admin Utama',
        'lembaga_id' => 1,
        'jenis_kelamin' => 'l',
    ]);

    $this->admin = User::factory()->create([
        'role_id' => 1,
        'authable_type' => Anggota::class,
        'authable_id' => $this->adminAnggota->id,
    ]);

    $this->siswa = Siswa::create([
        'nama' => 'Fulan Siswa',
        'nis' => '12345',
        'status' => 1,
        'lembaga_id' => 1,
        'jenis_kelamin' => 'l',
    ]);

    $this->userSiswa = User::create([
        'username' => '12345',
        'role_id' => 5,
        'password' => bcrypt('password'),
        'authable_type' => Siswa::class,
        'authable_id' => $this->siswa->id,
    ]);
});

it('mengizinkan admin impersonate siswa dan mengarahkan ke dashboard siswa dengan layout mobile', function () {
    $this->actingAs($this->admin);

    $response = $this->get(route('admin.user.impersonate', $this->userSiswa->id));

    $response->assertRedirect(route('siswa.dashboard'));
    expect(auth()->id())->toEqual($this->userSiswa->id);
    expect(session('impersonate_by'))->toEqual($this->admin->id);

    // Buka dashboard siswa dan pastikan banner impersonasi muncul
    $dashboardResponse = $this->get(route('siswa.dashboard'));
    $dashboardResponse->assertOk();
    $dashboardResponse->assertSee('Mode Impersonasi Siswa');
    $dashboardResponse->assertSee('Fulan Siswa');
    $dashboardResponse->assertSee('Kembali ke Admin');
});

it('mengizinkan admin keluar dari mode impersonasi dan kembali ke halaman /admin/user', function () {
    $this->actingAs($this->admin);

    // Impersonate
    $this->get(route('admin.user.impersonate', $this->userSiswa->id));
    expect(auth()->id())->toEqual($this->userSiswa->id);

    // Leave impersonate
    $response = $this->get(route('leave-impersonate'));

    $response->assertRedirect(route('admin.user.index'));
    expect(auth()->id())->toEqual($this->admin->id);
    expect(session()->has('impersonate_by'))->toBeFalse();
});

it('menolak impersonate jika dilakukan oleh user yang bukan admin', function () {
    $guru = User::factory()->create(['role_id' => 4]);
    $this->actingAs($guru);

    $response = $this->get(route('admin.user.impersonate', $this->userSiswa->id));
    $response->assertForbidden();
});

it('mendukung pencarian nama siswa dan filter role di UserList', function () {
    $this->actingAs($this->admin);

    Livewire::test(UserList::class)
        ->set('search', 'Fulan Siswa')
        ->assertSee('12345')
        ->assertSee('Fulan Siswa')
        ->set('filter_role', '5')
        ->assertSee('12345')
        ->set('filter_role', '1')
        ->assertDontSee('Fulan Siswa');
});
