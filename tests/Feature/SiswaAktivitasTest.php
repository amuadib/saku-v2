<?php

use App\Livewire\Admin\Siswa\SiswaAktivitas as SiswaAktivitasComponent;
use App\Livewire\Admin\Siswa\SiswaList;
use App\Livewire\Siswa\Profil;
use App\Livewire\Siswa\ProfilDetail;
use App\Models\Siswa;
use App\Models\SiswaAktivitas;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('student login records activity log and updates last_login_at on siswa', function () {
    $siswa = Siswa::create([
        'nis' => '10001',
        'nama' => 'Ahmad Fauzi',
        'status' => 1,
        'lembaga_id' => 1,
        'jenis_kelamin' => 'l',
    ]);

    $user = User::factory()->create([
        'role_id' => 5,
        'authable_type' => Siswa::class,
        'authable_id' => $siswa->id,
    ]);

    expect($siswa->last_login_at)->toBeNull();

    // Fire login event as when auth()->login($user) is called
    event(new Login('web', $user, false));

    $siswa->refresh();
    expect($siswa->last_login_at)->not->toBeNull();
    expect(SiswaAktivitas::where('siswa_id', $siswa->id)->where('tipe', 'login')->count())->toBe(1);

    $log = SiswaAktivitas::where('siswa_id', $siswa->id)->where('tipe', 'login')->first();
    expect($log->user_id)->toBe($user->id);
    expect($log->keterangan)->toContain('Login');
});

test('middleware logs student page navigation with friendly description', function () {
    $siswa = Siswa::create([
        'nis' => '10002',
        'nama' => 'Siti Nurhaliza',
        'status' => 1,
        'lembaga_id' => 1,
        'jenis_kelamin' => 'p',
    ]);

    $user = User::factory()->create([
        'role_id' => 5,
        'authable_type' => Siswa::class,
        'authable_id' => $siswa->id,
    ]);

    $this->actingAs($user)
        ->get(route('siswa.dashboard'))
        ->assertOk();

    $log = SiswaAktivitas::where('siswa_id', $siswa->id)
        ->where('tipe', 'akses_link')
        ->first();

    expect($log)->not->toBeNull();
    expect($log->route_name)->toBe('siswa.dashboard');
    expect($log->keterangan)->toBe('Membuka Dashboard Siswa');
    expect($log->method)->toBe('GET');
    expect($log->url)->toContain('dashboard-siswa');
});

test('middleware ignores livewire internal update requests', function () {
    $siswa = Siswa::create([
        'nis' => '10005',
        'nama' => 'Bambang Sudirman',
        'status' => 1,
        'lembaga_id' => 1,
        'jenis_kelamin' => 'l',
    ]);

    $user = User::factory()->create([
        'role_id' => 5,
        'authable_type' => Siswa::class,
        'authable_id' => $siswa->id,
    ]);

    $this->actingAs($user)
        ->post('/livewire-1234abcd/update', [], ['X-Livewire' => 'true']);

    expect(SiswaAktivitas::where('siswa_id', $siswa->id)->count())->toBe(0);
});

test('admin can view student activity page and filter by type', function () {
    $siswa = Siswa::create([
        'nis' => '10003',
        'nama' => 'Dewi Sartika',
        'status' => 1,
        'lembaga_id' => 1,
        'jenis_kelamin' => 'p',
    ]);

    $user = User::factory()->create([
        'role_id' => 5,
        'authable_type' => Siswa::class,
        'authable_id' => $siswa->id,
    ]);

    $admin = User::factory()->create([
        'role_id' => 1,
    ]);

    SiswaAktivitas::create([
        'siswa_id' => $siswa->id,
        'user_id' => $user->id,
        'tipe' => 'login',
        'url' => 'http://localhost/login',
        'route_name' => 'login',
        'method' => 'GET',
        'ip_address' => '192.168.1.10',
        'device' => 'Android',
        'browser' => 'Chrome',
        'keterangan' => 'Login ke Aplikasi',
    ]);

    SiswaAktivitas::create([
        'siswa_id' => $siswa->id,
        'user_id' => $user->id,
        'tipe' => 'akses_link',
        'url' => 'http://localhost/tagihan',
        'route_name' => 'siswa.tagihan',
        'method' => 'GET',
        'ip_address' => '192.168.1.10',
        'device' => 'Android',
        'browser' => 'Chrome',
        'keterangan' => 'Melihat Daftar Tagihan',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.siswa.aktivitas', $siswa->id))
        ->assertOk()
        ->assertSee('Dewi Sartika')
        ->assertSee('Melihat Daftar Tagihan');

    Livewire::actingAs($admin)
        ->test(SiswaAktivitasComponent::class, ['siswa' => $siswa])
        ->assertSee('Melihat Daftar Tagihan')
        ->set('filter_tipe', 'login')
        ->assertSee('Login ke Aplikasi')
        ->assertDontSee('Melihat Daftar Tagihan');
});

test('siswa list table displays last login time and link to activity page', function () {
    $siswa = Siswa::create([
        'nis' => '10004',
        'nama' => 'Rian Pratama',
        'status' => 1,
        'lembaga_id' => 1,
        'jenis_kelamin' => 'l',
        'last_login_at' => now()->subHours(2),
        'last_login_ip' => '10.0.0.5',
    ]);

    $admin = User::factory()->create([
        'role_id' => 1,
    ]);

    Livewire::actingAs($admin)
        ->test(SiswaList::class)
        ->assertSee('Rian Pratama')
        ->assertSee(route('admin.siswa.aktivitas', $siswa->id));
});

test('student updating profile records ubah_data activity log with changed fields', function () {
    $siswa = Siswa::create([
        'nis' => '10006',
        'nama' => 'Hendra Setiawan',
        'status' => 1,
        'lembaga_id' => 1,
        'jenis_kelamin' => 'l',
        'alamat' => 'Alamat Lama',
        'telepon' => '08111111111',
    ]);

    $user = User::factory()->create([
        'role_id' => 5,
        'authable_type' => Siswa::class,
        'authable_id' => $siswa->id,
    ]);

    Livewire::actingAs($user)
        ->test(ProfilDetail::class)
        ->set('alamat', 'Jl. Baru No. 45')
        ->set('telepon', '08999999999')
        ->call('save');

    $log = SiswaAktivitas::where('siswa_id', $siswa->id)
        ->where('tipe', 'ubah_data')
        ->first();

    expect($log)->not->toBeNull();
    expect($log->keterangan)->toContain('Memperbarui profil');
    expect($log->keterangan)->toContain('Alamat');
    expect($log->keterangan)->toContain('No. Telepon/WA');
    expect($log->data_lama['alamat'])->toBe('Alamat Lama');
    expect($log->data_baru['alamat'])->toBe('Jl. Baru No. 45');
    expect($log->url)->not->toContain('livewire');

    $admin = User::factory()->create(['role_id' => 1]);
    Livewire::actingAs($admin)
        ->test(SiswaAktivitasComponent::class, ['siswa' => $siswa])
        ->assertSee('Alamat Lama')
        ->assertSee('Jl. Baru No. 45');
});

test('student regenerating magic token records ubah_data activity log', function () {
    $siswa = Siswa::create([
        'nis' => '10007',
        'nama' => 'Ratna Sari',
        'status' => 1,
        'lembaga_id' => 1,
        'jenis_kelamin' => 'p',
    ]);

    $user = User::factory()->create([
        'role_id' => 5,
        'authable_type' => Siswa::class,
        'authable_id' => $siswa->id,
    ]);

    Livewire::actingAs($user)
        ->test(Profil::class)
        ->call('regenerateAndRedirect');

    $log = SiswaAktivitas::where('siswa_id', $siswa->id)
        ->where('tipe', 'ubah_data')
        ->first();

    expect($log)->not->toBeNull();
    expect($log->keterangan)->toContain('Membuat ulang (regenerate)');
});
