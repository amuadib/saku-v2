<?php

use App\Jobs\ProsesAdminTabungan;
use App\Models\Kas;
use App\Models\Kelas;
use App\Models\Periode;
use App\Models\Setting;
use App\Models\Siswa;
use App\Models\Tabungan;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config([
        'whatsapp.WA_API_URL' => '',
        'custom.tabungan.potongan.min_saldo_tidak_kena_admin' => 50000,
        'custom.tabungan.potongan.jumlah_per_tahun' => 10000,
        'custom.tabungan.potongan.lembaga' => [1],
    ]);

    $this->user = User::factory()->create([
        'role_id' => 1,
    ]);

    $this->periode = Periode::create([
        'nama' => '2026/2027',
        'mulai' => '2026-07-01',
        'selesai' => '2027-06-30',
        'aktif' => true,
    ]);

    $this->kelas = Kelas::create([
        'nama' => '7A',
        'periode_id' => $this->periode->id,
        'lembaga_id' => 1,
        'tingkat' => 7,
    ]);

    $this->kasAdmin = Kas::create([
        'nama' => 'Kas Administrasi',
        'lembaga_id' => 1,
        'saldo' => 0,
    ]);

    $this->kasTabungan = Kas::create([
        'nama' => 'Kas Tabungan Siswa',
        'lembaga_id' => 1,
        'saldo' => 500000,
        'tabungan' => true,
    ]);

    config(['custom.tabungan.potongan.kas_admin_id' => $this->kasAdmin->id]);
});

it('memotong saldo tabungan dan mencatat transaksi ganda pada riwayat tabungan dan buku besar kas', function () {
    $siswa = Siswa::create([
        'nis' => '1001',
        'nama' => 'Ahmad Santoso',
        'status' => 1,
        'lembaga_id' => 1,
        'kelas_id' => $this->kelas->id,
        'jenis_kelamin' => 'l',
    ]);

    $tabungan = Tabungan::create([
        'siswa_id' => $siswa->id,
        'kas_id' => $this->kasTabungan->id,
        'saldo' => 100000,
    ]);

    $job = new ProsesAdminTabungan(user_id: $this->user->id, tahun: 2026);
    $job->handle();

    // 1. Saldo tabungan berkurang 10.000 (100.000 -> 90.000)
    expect((float) $tabungan->fresh()->saldo)->toEqual(90000);

    // 2. Transaksi keluar tercatat untuk Tabungan (Riwayat Tabungan Siswa)
    $trxTabungan = Transaksi::where('transable_type', Tabungan::class)
        ->where('transable_id', $tabungan->id)
        ->first();

    expect($trxTabungan)->not->toBeNull();
    expect($trxTabungan->kode)->toStartWith('ADM2026');
    expect((float) $trxTabungan->jumlah)->toEqual(-10000);
    expect($trxTabungan->keterangan)->toContain('Biaya Administrasi');

    // 3. Transaksi masuk tercatat untuk Kas Administrasi (Buku Besar Transaksi Kas)
    $trxKas = Transaksi::where('transable_type', Kas::class)
        ->where('transable_id', $this->kasAdmin->id)
        ->first();

    expect($trxKas)->not->toBeNull();
    expect($trxKas->kode)->toStartWith('MTX2026');
    expect((float) $trxKas->jumlah)->toEqual(10000);
    expect($trxKas->keterangan)->toContain('Penerimaan Administrasi');

    // 4. Saldo Kas Administrasi bertambah & Kas Tabungan berkurang
    expect((float) $this->kasAdmin->fresh()->saldo)->toEqual(10000);
    expect((float) $this->kasTabungan->fresh()->saldo)->toEqual(490000);

    // 5. Setting tahun tercatat
    expect(Setting::where('key', 'tabungan_admin_2026')->exists())->toBeTrue();
});

it('melewati pemotongan jika saldo siswa kurang dari atau sama dengan batas minimal', function () {
    $siswa = Siswa::create([
        'nis' => '1002',
        'nama' => 'Budi Pratama',
        'status' => 1,
        'lembaga_id' => 1,
        'kelas_id' => $this->kelas->id,
        'jenis_kelamin' => 'l',
    ]);

    $tabungan = Tabungan::create([
        'siswa_id' => $siswa->id,
        'kas_id' => $this->kasTabungan->id,
        'saldo' => 45000, // Di bawah min 50.000
    ]);

    $job = new ProsesAdminTabungan(user_id: $this->user->id, tahun: 2026);
    $job->handle();

    expect((float) $tabungan->fresh()->saldo)->toEqual(45000);
    expect(Transaksi::where('transable_type', Tabungan::class)->exists())->toBeFalse();
    expect(Transaksi::where('transable_type', Kas::class)->exists())->toBeFalse();
});

it('hanya memproses sekali dalam satu tahun (idempotent)', function () {
    $siswa = Siswa::create([
        'nis' => '1003',
        'nama' => 'Citra Dewi',
        'status' => 1,
        'lembaga_id' => 1,
        'kelas_id' => $this->kelas->id,
        'jenis_kelamin' => 'p',
    ]);

    $tabungan = Tabungan::create([
        'siswa_id' => $siswa->id,
        'kas_id' => $this->kasTabungan->id,
        'saldo' => 100000,
    ]);

    // Eksekusi pertama
    $job1 = new ProsesAdminTabungan(user_id: $this->user->id, tahun: 2026);
    $job1->handle();

    expect((float) $tabungan->fresh()->saldo)->toEqual(90000);
    expect(Transaksi::where('transable_type', Tabungan::class)->count())->toEqual(1);

    // Eksekusi kedua di tahun yang sama
    $job2 = new ProsesAdminTabungan(user_id: $this->user->id, tahun: 2026);
    $job2->handle();

    // Saldo tetap 90000 dan transaksi tidak bertambah
    expect((float) $tabungan->fresh()->saldo)->toEqual(90000);
    expect(Transaksi::where('transable_type', Tabungan::class)->count())->toEqual(1);
});

it('dapat dijalankan melalui artisan command tabungan:potong-admin', function () {
    $siswa = Siswa::create([
        'nis' => '1004',
        'nama' => 'Doni Saputra',
        'status' => 1,
        'lembaga_id' => 1,
        'kelas_id' => $this->kelas->id,
        'jenis_kelamin' => 'l',
    ]);

    $tabungan = Tabungan::create([
        'siswa_id' => $siswa->id,
        'kas_id' => $this->kasTabungan->id,
        'saldo' => 200000,
    ]);

    $this->artisan('tabungan:potong-admin --tahun=2026 --sync')
        ->expectsOutputToContain('Menjalankan pemotongan administrasi tabungan tahun 2026')
        ->assertSuccessful();

    expect((float) $tabungan->fresh()->saldo)->toEqual(190000);

    // Jalankan kedua kali tanpa --force
    $this->artisan('tabungan:potong-admin --tahun=2026 --sync')
        ->expectsOutputToContain('sudah pernah diproses')
        ->assertSuccessful();

    expect((float) $tabungan->fresh()->saldo)->toEqual(190000);

    // Jalankan dengan --force, rekening yang sudah terpotong ADM2026 tidak terpotong ganda
    $this->artisan('tabungan:potong-admin --tahun=2026 --force --sync')
        ->assertSuccessful();

    expect((float) $tabungan->fresh()->saldo)->toEqual(190000);
});

it('memproses rekening siswa yang memiliki beberapa tabungan secara independen', function () {
    $kasKedua = Kas::create([
        'nama' => 'Kas Tabungan Sukarela',
        'lembaga_id' => 1,
        'saldo' => 100000,
        'tabungan' => true,
    ]);

    $siswa = Siswa::create([
        'nis' => '1005',
        'nama' => 'Eka Rahma',
        'status' => 1,
        'lembaga_id' => 1,
        'kelas_id' => $this->kelas->id,
        'jenis_kelamin' => 'p',
    ]);

    // Tabungan 1: Saldo 100.000 (> 50.000) -> harus dipotong
    $tabungan1 = Tabungan::create([
        'siswa_id' => $siswa->id,
        'kas_id' => $this->kasTabungan->id,
        'saldo' => 100000,
    ]);

    // Tabungan 2: Saldo 30.000 (<= 50.000) -> tidak boleh dipotong
    $tabungan2 = Tabungan::create([
        'siswa_id' => $siswa->id,
        'kas_id' => $kasKedua->id,
        'saldo' => 30000,
    ]);

    $job = new ProsesAdminTabungan(user_id: $this->user->id, tahun: 2026);
    $job->handle();

    expect((float) $tabungan1->fresh()->saldo)->toEqual(90000);
    expect((float) $tabungan2->fresh()->saldo)->toEqual(30000);

    // Pastikan hanya 1 transaksi tabungan yang tercatat untuk siswa ini
    expect(Transaksi::where('transable_type', Tabungan::class)
        ->where('transable_id', $tabungan1->id)
        ->exists())->toBeTrue();

    expect(Transaksi::where('transable_type', Tabungan::class)
        ->where('transable_id', $tabungan2->id)
        ->exists())->toBeFalse();
});
