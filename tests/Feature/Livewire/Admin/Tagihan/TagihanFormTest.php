<?php

use App\Livewire\Admin\Tagihan\TagihanForm;
use App\Models\Kas;
use App\Models\Kelas;
use App\Models\Periode;
use App\Models\Siswa;
use App\Models\Tagihan;
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

it('renders the tagihan create form successfully with lembaga and radio box peserta tagihan', function () {
    $this->get(route('admin.tagihan.create'))
        ->assertOk()
        ->assertSee('Buat Tagihan Baru')
        ->assertSee('Pilihan Lembaga')
        ->assertSee('Peserta Tagihan')
        ->assertSee('Semua Siswa (di lembaga terpilih)')
        ->assertSee('Kelas tertentu')
        ->assertSee('Siswa');
});

it('can create tagihan for semua siswa in the selected lembaga', function () {
    $periode = Periode::forceCreate([
        'nama' => '2026/2027',
        'mulai' => '2026-07-01',
        'selesai' => '2027-06-30',
        'aktif' => 'y',
    ]);

    $kelasSdi = Kelas::create([
        'nama' => '1A',
        'lembaga_id' => 1,
        'tingkat' => 1,
        'periode_id' => $periode->id,
    ]);

    $siswa1 = Siswa::create([
        'nis' => '1001',
        'nama' => 'Ahmad Fikri',
        'status' => 1,
        'lembaga_id' => 1,
        'kelas_id' => $kelasSdi->id,
        'jenis_kelamin' => 'l',
    ]);

    $siswa2 = Siswa::create([
        'nis' => '1002',
        'nama' => 'Budi Santoso',
        'status' => 1,
        'lembaga_id' => 1,
        'kelas_id' => $kelasSdi->id,
        'jenis_kelamin' => 'l',
    ]);

    // Student from different lembaga (should not receive tagihan)
    $siswaSmpi = Siswa::create([
        'nis' => '2001',
        'nama' => 'Citra SMPI',
        'status' => 1,
        'lembaga_id' => 2,
        'jenis_kelamin' => 'p',
    ]);

    $kasSdi = Kas::create([
        'nama' => 'Kas SPP SDI',
        'lembaga_id' => 1,
        'saldo' => 0,
        'ada_tagihan' => true,
    ]);

    Livewire::test(TagihanForm::class)
        ->set('lembaga_id', '1')
        ->set('kas_id', $kasSdi->id)
        ->set('peserta_tipe', 'semua')
        ->set('jumlah', '150.000')
        ->set('keterangan', 'SPP Juli 2026')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('admin.tagihan.index'));

    expect(Tagihan::where('siswa_id', $siswa1->id)->count())->toBe(1);
    expect(Tagihan::where('siswa_id', $siswa2->id)->count())->toBe(1);
    expect(Tagihan::where('siswa_id', $siswaSmpi->id)->count())->toBe(0);

    $tagihan1 = Tagihan::where('siswa_id', $siswa1->id)->first();
    expect((int) $tagihan1->jumlah)->toBe(150000);
    expect((int) $tagihan1->bayar)->toBe(0);
    expect($tagihan1->kas_id)->toBe($kasSdi->id);
    expect($tagihan1->keterangan)->toBe('SPP Juli 2026');
});

it('can create tagihan for kelas tertentu in the selected lembaga', function () {
    $periode = Periode::forceCreate([
        'nama' => '2026/2027',
        'mulai' => '2026-07-01',
        'selesai' => '2027-06-30',
        'aktif' => 'y',
    ]);

    $kelas7A = Kelas::create([
        'nama' => '7A',
        'lembaga_id' => 2,
        'tingkat' => 7,
        'periode_id' => $periode->id,
    ]);

    $kelas7B = Kelas::create([
        'nama' => '7B',
        'lembaga_id' => 2,
        'tingkat' => 7,
        'periode_id' => $periode->id,
    ]);

    $siswa7A1 = Siswa::create([
        'nis' => '7001',
        'nama' => 'Siswa 7A-1',
        'status' => 1,
        'lembaga_id' => 2,
        'kelas_id' => $kelas7A->id,
        'jenis_kelamin' => 'l',
    ]);

    $siswa7A2 = Siswa::create([
        'nis' => '7002',
        'nama' => 'Siswa 7A-2',
        'status' => 1,
        'lembaga_id' => 2,
        'kelas_id' => $kelas7A->id,
        'jenis_kelamin' => 'p',
    ]);

    $siswa7B = Siswa::create([
        'nis' => '7003',
        'nama' => 'Siswa 7B-1',
        'status' => 1,
        'lembaga_id' => 2,
        'kelas_id' => $kelas7B->id,
        'jenis_kelamin' => 'l',
    ]);

    $kasSmpi = Kas::create([
        'nama' => 'Kas Praktikum SMPI',
        'lembaga_id' => 2,
        'saldo' => 0,
        'ada_tagihan' => true,
    ]);

    Livewire::test(TagihanForm::class)
        ->set('lembaga_id', '2')
        ->set('kas_id', $kasSmpi->id)
        ->set('peserta_tipe', 'kelas')
        ->set('kelas_id', $kelas7A->id)
        ->set('jumlah', '75.000')
        ->set('keterangan', 'Uang Praktikum IPA')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('admin.tagihan.index'));

    expect(Tagihan::where('siswa_id', $siswa7A1->id)->count())->toBe(1);
    expect(Tagihan::where('siswa_id', $siswa7A2->id)->count())->toBe(1);
    expect(Tagihan::where('siswa_id', $siswa7B->id)->count())->toBe(0);
});

it('can create tagihan for a single siswa in the selected lembaga', function () {
    $siswa = Siswa::create([
        'nis' => '1005',
        'nama' => 'Doni Siswa',
        'status' => 1,
        'lembaga_id' => 1,
        'jenis_kelamin' => 'l',
    ]);

    $kas = Kas::create([
        'nama' => 'Kas SPP SDI',
        'lembaga_id' => 1,
        'saldo' => 0,
        'ada_tagihan' => true,
    ]);

    Livewire::test(TagihanForm::class)
        ->set('lembaga_id', '1')
        ->set('kas_id', $kas->id)
        ->set('peserta_tipe', 'siswa')
        ->set('siswa_id', $siswa->id)
        ->set('jumlah', '200.000')
        ->set('bayar', '50.000')
        ->set('keterangan', 'Tagihan Seragam')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('admin.tagihan.index'));

    $tagihan = Tagihan::where('siswa_id', $siswa->id)->first();
    expect($tagihan)->not->toBeNull();
    expect((int) $tagihan->jumlah)->toBe(200000);
    expect((int) $tagihan->bayar)->toBe(50000);
    expect($tagihan->kas_id)->toBe($kas->id);
    expect($tagihan->keterangan)->toBe('Tagihan Seragam');
});

it('validates kas and target matches selected lembaga', function () {
    $siswaSdi = Siswa::create([
        'nis' => '1006',
        'nama' => 'Eka SDI',
        'status' => 1,
        'lembaga_id' => 1,
        'jenis_kelamin' => 'p',
    ]);

    $kasSmpi = Kas::create([
        'nama' => 'Kas Tagihan SMPI',
        'lembaga_id' => 2,
        'saldo' => 0,
        'ada_tagihan' => true,
    ]);

    Livewire::test(TagihanForm::class)
        ->set('lembaga_id', '1')
        ->set('kas_id', $kasSmpi->id)
        ->set('peserta_tipe', 'siswa')
        ->set('siswa_id', $siswaSdi->id)
        ->set('jumlah', '50.000')
        ->call('save')
        ->assertHasErrors(['kas_id']);
});

