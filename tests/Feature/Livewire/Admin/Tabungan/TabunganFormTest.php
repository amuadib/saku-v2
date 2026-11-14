<?php

use App\Livewire\Admin\Tabungan\TabunganForm;
use App\Models\Kas;
use App\Models\Kelas;
use App\Models\Periode;
use App\Models\Siswa;
use App\Models\Tabungan;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'role_id' => 1, // Admin
    ]);
    $this->actingAs($this->user);
});

it('renders the tabungan create form successfully with tom select data', function () {
    $periode = Periode::forceCreate([
        'nama' => '2026/2027',
        'mulai' => '2026-07-01',
        'selesai' => '2027-06-30',
        'aktif' => 'y',
    ]);

    $kelas = Kelas::create([
        'nama' => '7A',
        'lembaga_id' => 1,
        'tingkat' => 7,
        'periode_id' => $periode->id,
    ]);

    $siswa = Siswa::create([
        'nis' => '1001',
        'nama' => 'Ahmad Fikri',
        'status' => 1,
        'lembaga_id' => 1,
        'kelas_id' => $kelas->id,
        'jenis_kelamin' => 'l',
    ]);

    $this->get(route('admin.tabungan.create'))
        ->assertOk()
        ->assertSee('Tambah Tabungan')
        ->assertSee('Ahmad Fikri')
        ->assertSee('1001')
        ->assertSee('7A');
});

it('disables kas tabungan selection when no student is selected', function () {
    Livewire::test(TabunganForm::class)
        ->assertSet('siswa_id', '')
        ->assertSet('kas_id', '')
        ->assertSeeHtml('disabled')
        ->assertSee('Pilih siswa terlebih dahulu');
});

it('filters kas tabungan according to selected student lembaga and auto-selects if single option', function () {
    $siswaSdi = Siswa::create([
        'nis' => '1002',
        'nama' => 'Budi Santoso',
        'status' => 1,
        'lembaga_id' => 1, // SDI
        'jenis_kelamin' => 'l',
    ]);

    $siswaSmpi = Siswa::create([
        'nis' => '2002',
        'nama' => 'Dewi Sartika',
        'status' => 1,
        'lembaga_id' => 2, // SMPI
        'jenis_kelamin' => 'p',
    ]);

    $kasSdi = Kas::create([
        'nama' => 'Kas Tabungan SDI',
        'lembaga_id' => 1,
        'saldo' => 500000,
        'tabungan' => true,
    ]);

    $kasSmpi1 = Kas::create([
        'nama' => 'Kas Tabungan Reguler SMPI',
        'lembaga_id' => 2,
        'saldo' => 300000,
        'tabungan' => true,
    ]);

    $kasSmpi2 = Kas::create([
        'nama' => 'Kas Tabungan Qurban SMPI',
        'lembaga_id' => 2,
        'saldo' => 200000,
        'tabungan' => true,
    ]);

    // Test selection for SDI student: exactly 1 kas available -> auto-selected
    Livewire::test(TabunganForm::class)
        ->set('siswa_id', $siswaSdi->id)
        ->assertSet('kas_id', $kasSdi->id)
        ->assertSee('Kas Tabungan SDI')
        ->assertDontSee('Kas Tabungan Reguler SMPI');

    // Test selection for SMPI student: 2 kas available -> not auto-selected, shows both
    Livewire::test(TabunganForm::class)
        ->set('siswa_id', $siswaSmpi->id)
        ->assertSet('kas_id', '')
        ->assertSee('Kas Tabungan Reguler SMPI')
        ->assertSee('Kas Tabungan Qurban SMPI')
        ->assertDontSee('Kas Tabungan SDI');
});

it('resets kas_id if selected student is changed to another lembaga', function () {
    $siswaSdi = Siswa::create([
        'nis' => '1003',
        'nama' => 'Hendra',
        'status' => 1,
        'lembaga_id' => 1,
        'jenis_kelamin' => 'l',
    ]);

    $siswaSmpi = Siswa::create([
        'nis' => '2003',
        'nama' => 'Lestari',
        'status' => 1,
        'lembaga_id' => 2,
        'jenis_kelamin' => 'p',
    ]);

    $kasSdi = Kas::create([
        'nama' => 'Kas Tabungan SDI',
        'lembaga_id' => 1,
        'saldo' => 100000,
        'tabungan' => true,
    ]);

    Kas::create([
        'nama' => 'Kas Tabungan SMPI A',
        'lembaga_id' => 2,
        'saldo' => 100000,
        'tabungan' => true,
    ]);

    Kas::create([
        'nama' => 'Kas Tabungan SMPI B',
        'lembaga_id' => 2,
        'saldo' => 100000,
        'tabungan' => true,
    ]);

    Livewire::test(TabunganForm::class)
        ->set('siswa_id', $siswaSdi->id)
        ->assertSet('kas_id', $kasSdi->id)
        ->set('siswa_id', $siswaSmpi->id)
        ->assertSet('kas_id', '');
});

it('can successfully create a new tabungan record with raw unformatted numeric balance in DB', function () {
    $siswa = Siswa::create([
        'nis' => '1004',
        'nama' => 'Muhammad Rizky',
        'status' => 1,
        'lembaga_id' => 1,
        'jenis_kelamin' => 'l',
    ]);

    $kas = Kas::create([
        'nama' => 'Kas Tabungan SDI',
        'lembaga_id' => 1,
        'saldo' => 0,
        'tabungan' => true,
    ]);

    // Test formatted currency string input with thousand separator
    Livewire::test(TabunganForm::class)
        ->set('siswa_id', $siswa->id)
        ->set('kas_id', $kas->id)
        ->set('saldo', '1.500.000')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('admin.tabungan.index'));

    $tabungan = Tabungan::where('siswa_id', $siswa->id)->where('kas_id', $kas->id)->first();
    expect($tabungan)->not->toBeNull();
    // Raw numeric value in database without thousand separator dots
    expect((int) $tabungan->saldo)->toBe(1500000);

    // Verify transaction recorded in database
    $transaksi = Transaksi::where('transable_id', $tabungan->id)
        ->where('transable_type', Tabungan::class)
        ->first();
    expect($transaksi)->not->toBeNull();
    expect((int) $transaksi->jumlah)->toBe(1500000);
    expect($transaksi->kode)->toStartWith('STB');
    expect($transaksi->keterangan)->toContain('Setor Tabungan');
    expect($transaksi->user_id)->toBe($this->user->id);

    // Verify kas balance incremented
    expect((int) $kas->fresh()->saldo)->toBe(1500000);
});

it('fails validation when kas does not belong to student lembaga', function () {
    $siswaSdi = Siswa::create([
        'nis' => '1005',
        'nama' => 'Rina',
        'status' => 1,
        'lembaga_id' => 1,
        'jenis_kelamin' => 'p',
    ]);

    $kasSmpi = Kas::create([
        'nama' => 'Kas Tabungan SMPI',
        'lembaga_id' => 2,
        'saldo' => 0,
        'tabungan' => true,
    ]);

    Livewire::test(TabunganForm::class)
        ->set('siswa_id', $siswaSdi->id)
        ->set('kas_id', $kasSmpi->id)
        ->set('saldo', '10.000')
        ->call('save')
        ->assertHasErrors(['kas_id']);

    $this->assertDatabaseMissing('tabungan', [
        'siswa_id' => $siswaSdi->id,
        'kas_id' => $kasSmpi->id,
    ]);
});

it('adds to existing tabungan balance if student already has a tabungan account on that kas', function () {
    $siswa = Siswa::create([
        'nis' => '1006',
        'nama' => 'Toni',
        'status' => 1,
        'lembaga_id' => 1,
        'jenis_kelamin' => 'l',
    ]);

    $kas = Kas::create([
        'nama' => 'Kas Tabungan SDI',
        'lembaga_id' => 1,
        'saldo' => 0,
        'tabungan' => true,
    ]);

    $existingTabungan = Tabungan::create([
        'siswa_id' => $siswa->id,
        'kas_id' => $kas->id,
        'saldo' => 25000,
    ]);

    // Submitting with 15.000 formatted should increment the balance to 40000 (25000 + 15000)
    Livewire::test(TabunganForm::class)
        ->set('siswa_id', $siswa->id)
        ->set('kas_id', $kas->id)
        ->assertSee('Siswa ini sudah memiliki rekening tabungan')
        ->assertSee('Tambah Saldo Tabungan')
        ->set('saldo', '15.000')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('admin.tabungan.index'));

    // Verify no duplicate account was created
    expect(Tabungan::where('siswa_id', $siswa->id)->where('kas_id', $kas->id)->count())->toBe(1);

    // Verify existing tabungan balance was incremented to raw 40000
    expect((int) $existingTabungan->fresh()->saldo)->toBe(40000);

    // Verify transaction recorded for the incremented amount
    $transaksi = Transaksi::where('transable_id', $existingTabungan->id)
        ->where('transable_type', Tabungan::class)
        ->first();
    expect($transaksi)->not->toBeNull();
    expect((int) $transaksi->jumlah)->toBe(15000);
    expect($transaksi->kode)->toStartWith('STB');
    expect((int) $kas->fresh()->saldo)->toBe(15000);
});

it('validates negative or empty saldo', function () {
    $siswa = Siswa::create([
        'nis' => '1007',
        'nama' => 'Sinta',
        'status' => 1,
        'lembaga_id' => 1,
        'jenis_kelamin' => 'p',
    ]);

    $kas = Kas::create([
        'nama' => 'Kas Tabungan SDI',
        'lembaga_id' => 1,
        'saldo' => 0,
        'tabungan' => true,
    ]);

    Livewire::test(TabunganForm::class)
        ->set('siswa_id', $siswa->id)
        ->set('kas_id', $kas->id)
        ->set('saldo', '-5000')
        ->call('save')
        ->assertHasErrors(['saldo']);

    Livewire::test(TabunganForm::class)
        ->set('siswa_id', $siswa->id)
        ->set('kas_id', $kas->id)
        ->set('saldo', '')
        ->call('save')
        ->assertHasErrors(['saldo']);
});
