<?php

use App\Http\Middleware\OtomatisPotongAdminTabungan;
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
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::flush();

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
        'nama' => '4A',
        'periode_id' => $this->periode->id,
        'lembaga_id' => 1,
        'tingkat' => 4,
    ]);

    $this->kasPenerima = Kas::create([
        'nama' => 'BOS',
        'lembaga_id' => 1,
        'saldo' => 1000000,
    ]);

    $this->kasTabungan = Kas::create([
        'nama' => 'Tabungan Wajib',
        'lembaga_id' => 1,
        'saldo' => 500000,
        'tabungan' => true,
    ]);

    config(['custom.tabungan.potongan.kas_admin_id' => $this->kasPenerima->id]);
});

it('mode dry-run menampilkan simulasi tanpa mengubah saldo maupun membuat transaksi', function () {
    $siswa = Siswa::create([
        'nis' => '2001',
        'nama' => 'Siswa Terpotong',
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

    (new ProsesAdminTabungan(user_id: $this->user->id, tahun: 2026))->handle();

    expect((int) $tabungan->fresh()->saldo)->toEqual(90000);
    expect((int) $this->kasTabungan->fresh()->saldo)->toEqual(490000);
    expect((int) $this->kasPenerima->fresh()->saldo)->toEqual(1010000);

    $this->artisan('tabungan:rollback-admin 2026 --dry-run')
        ->expectsOutputToContain('SIMULASI (DRY-RUN)')
        ->expectsOutputToContain('Tidak ada perubahan pada database')
        ->assertSuccessful();

    // Pastikan tidak ada yang berubah
    expect((int) $tabungan->fresh()->saldo)->toEqual(90000);
    expect((int) $this->kasTabungan->fresh()->saldo)->toEqual(490000);
    expect((int) $this->kasPenerima->fresh()->saldo)->toEqual(1010000);
    expect(Transaksi::where('kode', 'like', 'MRV2026%')->exists())->toBeFalse();
    expect(Transaksi::where('kode', 'like', 'KRV2026%')->exists())->toBeFalse();
});

it('melakukan jurnal balik opsi B hanya untuk rekening yang dipotong dan menjaga penanda tahun 2026', function () {
    $siswaTerpotong = Siswa::create([
        'nis' => '2002',
        'nama' => 'Siswa Kena Potong',
        'status' => 1,
        'lembaga_id' => 1,
        'kelas_id' => $this->kelas->id,
        'jenis_kelamin' => 'l',
    ]);

    $siswaTidakTerpotong = Siswa::create([
        'nis' => '2003',
        'nama' => 'Siswa Saldo Kecil',
        'status' => 1,
        'lembaga_id' => 1,
        'kelas_id' => $this->kelas->id,
        'jenis_kelamin' => 'p',
    ]);

    $tabunganTerpotong = Tabungan::create([
        'siswa_id' => $siswaTerpotong->id,
        'kas_id' => $this->kasTabungan->id,
        'saldo' => 150000, // > 50.000 -> akan dipotong
    ]);

    $tabunganTidakTerpotong = Tabungan::create([
        'siswa_id' => $siswaTidakTerpotong->id,
        'kas_id' => $this->kasTabungan->id,
        'saldo' => 30000, // <= 50.000 -> tidak dipotong
    ]);

    // Jalankan pemotongan
    (new ProsesAdminTabungan(user_id: $this->user->id, tahun: 2026))->handle();

    expect((int) $tabunganTerpotong->fresh()->saldo)->toEqual(140000);
    expect((int) $tabunganTidakTerpotong->fresh()->saldo)->toEqual(30000);
    expect((int) $this->kasTabungan->fresh()->saldo)->toEqual(490000);
    expect((int) $this->kasPenerima->fresh()->saldo)->toEqual(1010000);

    // Jalankan rollback Opsi B
    $this->artisan('tabungan:rollback-admin 2026 --force')
        ->expectsOutputToContain('BERHASIL')
        ->assertSuccessful();

    // 1. Saldo siswa yang terpotong kembali ke 150.000; siswa yang tidak terpotong TETAP 30.000
    expect((int) $tabunganTerpotong->fresh()->saldo)->toEqual(150000);
    expect((int) $tabunganTidakTerpotong->fresh()->saldo)->toEqual(30000);

    // 2. Saldo kedua kas kembali seperti semula
    expect((int) $this->kasTabungan->fresh()->saldo)->toEqual(500000);
    expect((int) $this->kasPenerima->fresh()->saldo)->toEqual(1000000);

    // 3. Transaksi asal (ADM & MTX) tetap ada (Opsi B) dan jurnal balik (MRV & KRV) tercipta
    $adm = Transaksi::where('kode', 'like', 'ADM2026%')->first();
    $mrv = Transaksi::where('kode', 'like', 'MRV2026%')->first();
    $krv = Transaksi::where('kode', 'like', 'KRV2026%')->first();

    expect($adm)->not->toBeNull();
    expect($mrv)->not->toBeNull();
    expect($mrv->kode)->toEqual('MRV'.substr($adm->kode, 3));
    expect((int) $mrv->jumlah)->toEqual(10000);
    expect($mrv->transable_id)->toEqual($tabunganTerpotong->id);
    expect($mrv->keterangan)->toStartWith('[REVERSAL]');

    expect($krv)->not->toBeNull();
    expect((int) $krv->jumlah)->toEqual(-10000);
    expect($krv->transable_id)->toEqual($this->kasPenerima->id);
    expect($krv->keterangan)->toStartWith('[REVERSAL]');

    // 4. Setting tabungan_admin_2026 tetap ada (mencegah pemotongan ulang) dengan metadata rollback
    $setting = Setting::where('key', 'tabungan_admin_2026')->first();
    expect($setting)->not->toBeNull();
    expect($setting->payload['status'])->toEqual('rolled_back_already_processed');
    expect($setting->payload['total_rekening_rollback'])->toEqual(1);
    expect(Cache::has('tabungan_admin_checked_2026'))->toBeTrue();

    // 5. Idempotency: jalankan kedua kali tidak boleh mengembalikan saldo lagi
    $this->artisan('tabungan:rollback-admin 2026 --force')
        ->expectsOutputToContain('sudah pernah di-rollback sebelumnya')
        ->assertSuccessful();

    expect((int) $tabunganTerpotong->fresh()->saldo)->toEqual(150000);
    expect((int) $this->kasTabungan->fresh()->saldo)->toEqual(500000);
    expect((int) $this->kasPenerima->fresh()->saldo)->toEqual(1000000);
    expect(Transaksi::where('kode', 'like', 'MRV2026%')->count())->toEqual(1);
});

it('pencegahan: mendeteksi potongan format lama KTB di tahun yang sama agar tidak dipotong ulang', function () {
    Queue::fake();

    $tahun = (int) date('Y');

    $siswa = Siswa::create([
        'nis' => '2004',
        'nama' => 'Siswa Sudah Dipotong Januari',
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

    // Simulasikan potongan format lama bulan Januari (tanpa Setting tabungan_admin_{tahun})
    Transaksi::create([
        'kode' => 'KTB1'.$tahun.'01190001',
        'transable_type' => Tabungan::class,
        'transable_id' => $tabungan->id,
        'jumlah' => 10000,
        'keterangan' => 'Administrasi Tabungan Wajib '.$siswa->nama,
        'user_id' => $this->user->id,
    ]);

    // 1. Job harus mendeteksi sudah diproses
    $job = new ProsesAdminTabungan(user_id: $this->user->id, tahun: $tahun);
    expect($job->sudahDiprosesTahunIni($tahun))->toBeTrue();

    $job->handle();
    expect((int) $tabungan->fresh()->saldo)->toEqual(200000);
    expect(Transaksi::where('kode', 'like', "ADM{$tahun}%")->exists())->toBeFalse();

    // 2. Middleware OtomatisPotongAdminTabungan juga tidak boleh men-dispatch job
    $request = Request::create('/admin', 'GET');
    $request->setUserResolver(fn () => $this->user);

    $middleware = new OtomatisPotongAdminTabungan;
    $middleware->handle($request, fn () => new Response('OK', 200));
    app()->terminate();

    Queue::assertNothingPushed();
    expect(Cache::has('tabungan_admin_checked_'.$tahun))->toBeTrue();
});
