<?php

use App\Livewire\Admin\Tagihan\TagihanRekap;
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

it('renders the rekap tagihan page with kas columns and student tagihan data', function () {
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

    $kasSpp = Kas::create([
        'nama' => 'Kas SPP',
        'lembaga_id' => 1,
        'saldo' => 0,
        'ada_tagihan' => true,
    ]);

    $kasGedung = Kas::create([
        'nama' => 'Kas Uang Gedung',
        'lembaga_id' => 1,
        'saldo' => 0,
        'ada_tagihan' => true,
    ]);

    // Siswa 1: Mempunyai tunggakan SPP & Gedung
    $siswa1 = Siswa::create([
        'nis' => '1001',
        'nama' => 'Ahmad Fikri',
        'status' => 1,
        'lembaga_id' => 1,
        'kelas_id' => $kelas->id,
        'jenis_kelamin' => 'l',
        'telepon' => '081234567890',
    ]);

    Tagihan::create([
        'kode' => 'TAG-001',
        'siswa_id' => $siswa1->id,
        'kas_id' => $kasSpp->id,
        'jumlah' => 150000,
        'bayar' => 0,
        'keterangan' => 'SPP Juli 2026',
        'user_id' => $this->user->id,
    ]);

    Tagihan::create([
        'kode' => 'TAG-002',
        'siswa_id' => $siswa1->id,
        'kas_id' => $kasGedung->id,
        'jumlah' => 500000,
        'bayar' => 200000, // sisa 300.000
        'keterangan' => 'Gedung Tahap 1',
        'user_id' => $this->user->id,
    ]);

    // Siswa 2: Tagihan sudah LUNAS (tidak boleh muncul di rekap tanggungan)
    $siswaLunas = Siswa::create([
        'nis' => '1002',
        'nama' => 'Budi Santoso',
        'status' => 1,
        'lembaga_id' => 1,
        'kelas_id' => $kelas->id,
        'jenis_kelamin' => 'l',
    ]);

    Tagihan::create([
        'kode' => 'TAG-003',
        'siswa_id' => $siswaLunas->id,
        'kas_id' => $kasSpp->id,
        'jumlah' => 150000,
        'bayar' => 150000, // lunas
        'user_id' => $this->user->id,
    ]);

    $this->get(route('admin.tagihan.rekap'))
        ->assertOk()
        ->assertSee('Rekap Tagihan Siswa')
        ->assertSee('Kas SPP')
        ->assertSee('Kas Uang Gedung')
        ->assertSee('Ahmad Fikri')
        ->assertSee('Rp 150.000')
        ->assertSee('Rp 300.000')
        ->assertSee('Rp 450.000') // Total Tagihan Ahmad
        ->assertSee('Kirim WA')
        ->assertDontSee('Budi Santoso'); // Karena lunas
});

it('can trigger kirimWa for student with valid phone', function () {
    $siswa = Siswa::create([
        'nis' => '1003',
        'nama' => 'Citra Lestari',
        'status' => 1,
        'lembaga_id' => 1,
        'jenis_kelamin' => 'p',
        'telepon' => '08987654321',
    ]);

    User::create([
        'role_id' => 5,
        'username' => 'citra1003',
        'password' => bcrypt('citra1003'),
        'authable_type' => Siswa::class,
        'authable_id' => $siswa->id,
    ]);

    $kas = Kas::create([
        'nama' => 'Kas SPP',
        'lembaga_id' => 1,
        'saldo' => 0,
        'ada_tagihan' => true,
    ]);

    Tagihan::create([
        'kode' => 'TAG-004',
        'siswa_id' => $siswa->id,
        'kas_id' => $kas->id,
        'jumlah' => 200000,
        'bayar' => 0,
        'keterangan' => 'SPP Agustus 2026',
        'user_id' => $this->user->id,
    ]);

    Livewire::test(TagihanRekap::class)
        ->call('kirimWa', $siswa->id)
        ->assertHasNoErrors();
});

it('shows warning when triggering kirimWa for student without phone number', function () {
    $siswa = Siswa::create([
        'nis' => '1004',
        'nama' => 'Dedi Tanpa Telepon',
        'status' => 1,
        'lembaga_id' => 1,
        'jenis_kelamin' => 'l',
        'telepon' => null,
    ]);

    $kas = Kas::create([
        'nama' => 'Kas SPP',
        'lembaga_id' => 1,
        'saldo' => 0,
        'ada_tagihan' => true,
    ]);

    Tagihan::create([
        'kode' => 'TAG-005',
        'siswa_id' => $siswa->id,
        'kas_id' => $kas->id,
        'jumlah' => 100000,
        'bayar' => 0,
        'user_id' => $this->user->id,
    ]);

    Livewire::test(TagihanRekap::class)
        ->call('kirimWa', $siswa->id)
        ->assertHasNoErrors();
});

it('filters students by search in rekap tagihan', function () {
    $periode = Periode::forceCreate([
        'nama' => '2026/2027',
        'mulai' => '2026-07-01',
        'selesai' => '2027-06-30',
        'aktif' => 'y',
    ]);

    $kelasA = Kelas::create([
        'nama' => '8A',
        'lembaga_id' => 1,
        'tingkat' => 8,
        'periode_id' => $periode->id,
    ]);

    $kelasB = Kelas::create([
        'nama' => '8B',
        'lembaga_id' => 1,
        'tingkat' => 8,
        'periode_id' => $periode->id,
    ]);

    $kas = Kas::create([
        'nama' => 'Kas SPP',
        'lembaga_id' => 1,
        'saldo' => 0,
        'ada_tagihan' => true,
    ]);

    $siswaA = Siswa::create([
        'nis' => '8001',
        'nama' => 'Siswa Kelas 8A',
        'status' => 1,
        'lembaga_id' => 1,
        'kelas_id' => $kelasA->id,
        'jenis_kelamin' => 'l',
    ]);

    $siswaB = Siswa::create([
        'nis' => '8002',
        'nama' => 'Siswa Kelas 8B',
        'status' => 1,
        'lembaga_id' => 1,
        'kelas_id' => $kelasB->id,
        'jenis_kelamin' => 'p',
    ]);

    Tagihan::create([
        'kode' => 'TAG-801',
        'siswa_id' => $siswaA->id,
        'kas_id' => $kas->id,
        'jumlah' => 100000,
        'bayar' => 0,
        'user_id' => $this->user->id,
    ]);

    Tagihan::create([
        'kode' => 'TAG-802',
        'siswa_id' => $siswaB->id,
        'kas_id' => $kas->id,
        'jumlah' => 100000,
        'bayar' => 0,
        'user_id' => $this->user->id,
    ]);

    Livewire::test(TagihanRekap::class)
        ->set('search', '8001')
        ->assertSee('Siswa Kelas 8A')
        ->assertDontSee('Siswa Kelas 8B');
});

it('can sort rekap tagihan by nama, kelas, kas, and total', function () {
    $periode = Periode::forceCreate([
        'nama' => '2026/2027',
        'mulai' => '2026-07-01',
        'selesai' => '2027-06-30',
        'aktif' => 'y',
    ]);

    $kelasA = Kelas::create([
        'nama' => '7A',
        'lembaga_id' => 1,
        'tingkat' => 7,
        'periode_id' => $periode->id,
    ]);

    $kelasB = Kelas::create([
        'nama' => '7B',
        'lembaga_id' => 1,
        'tingkat' => 7,
        'periode_id' => $periode->id,
    ]);

    $kasSpp = Kas::create([
        'nama' => 'Kas SPP',
        'lembaga_id' => 1,
        'saldo' => 0,
        'ada_tagihan' => true,
    ]);

    $kasGedung = Kas::create([
        'nama' => 'Kas Gedung',
        'lembaga_id' => 1,
        'saldo' => 0,
        'ada_tagihan' => true,
    ]);

    // Student 1: Zaky in 7B with SPP 50.000, Gedung 500.000 -> Total 550.000
    $siswaZaky = Siswa::create([
        'nis' => '9001',
        'nama' => 'Zaky',
        'status' => 1,
        'lembaga_id' => 1,
        'kelas_id' => $kelasB->id,
        'jenis_kelamin' => 'l',
    ]);
    Tagihan::create([
        'siswa_id' => $siswaZaky->id,
        'kas_id' => $kasSpp->id,
        'jumlah' => 50000,
        'bayar' => 0,
        'user_id' => $this->user->id,
    ]);
    Tagihan::create([
        'siswa_id' => $siswaZaky->id,
        'kas_id' => $kasGedung->id,
        'jumlah' => 500000,
        'bayar' => 0,
        'user_id' => $this->user->id,
    ]);

    // Student 2: Andi in 7A with SPP 200.000, Gedung 0 -> Total 200.000
    $siswaAndi = Siswa::create([
        'nis' => '9002',
        'nama' => 'Andi',
        'status' => 1,
        'lembaga_id' => 1,
        'kelas_id' => $kelasA->id,
        'jenis_kelamin' => 'l',
    ]);
    Tagihan::create([
        'siswa_id' => $siswaAndi->id,
        'kas_id' => $kasSpp->id,
        'jumlah' => 200000,
        'bayar' => 0,
        'user_id' => $this->user->id,
    ]);

    // Default sort is total DESC -> Zaky (550.000) first, Andi (200.000) second
    $test = Livewire::test(TagihanRekap::class)
        ->assertSet('sortField', 'total')
        ->assertSet('sortDirection', 'desc')
        ->assertSeeInOrder(['Zaky', 'Andi']);

    // Sort by nama (asc) -> Andi first, Zaky second
    $test->call('sortBy', 'nama')
        ->assertSet('sortField', 'nama')
        ->assertSet('sortDirection', 'asc')
        ->assertSeeInOrder(['Andi', 'Zaky']);

    // Toggle nama -> desc -> Zaky first, Andi second
    $test->call('sortBy', 'nama')
        ->assertSet('sortDirection', 'desc')
        ->assertSeeInOrder(['Zaky', 'Andi']);

    // Sort by kelas (asc) -> 7A (Andi) first, 7B (Zaky) second
    $test->call('sortBy', 'kelas')
        ->assertSet('sortField', 'kelas')
        ->assertSet('sortDirection', 'asc')
        ->assertSeeInOrder(['Andi', 'Zaky']);

    // Sort by total (default desc) -> Zaky (550.000) first, Andi (200.000) second
    $test->call('sortBy', 'total')
        ->assertSet('sortField', 'total')
        ->assertSet('sortDirection', 'desc')
        ->assertSeeInOrder(['Zaky', 'Andi']);

    // Toggle total -> asc -> Andi (200.000) first, Zaky (550.000) second
    $test->call('sortBy', 'total')
        ->assertSet('sortField', 'total')
        ->assertSet('sortDirection', 'asc')
        ->assertSeeInOrder(['Andi', 'Zaky']);

    // Sort by Kas SPP (default desc) -> Andi (200.000) first, Zaky (50.000) second
    $test->call('sortBy', 'kas_'.$kasSpp->id)
        ->assertSet('sortField', 'kas_'.$kasSpp->id)
        ->assertSet('sortDirection', 'desc')
        ->assertSeeInOrder(['Andi', 'Zaky']);

    // Sort by Kas Gedung (default desc) -> Zaky (500.000) first, Andi (0) second
    $test->call('sortBy', 'kas_'.$kasGedung->id)
        ->assertSet('sortField', 'kas_'.$kasGedung->id)
        ->assertSet('sortDirection', 'desc')
        ->assertSeeInOrder(['Zaky', 'Andi']);
});
