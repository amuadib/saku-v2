<?php

use App\Livewire\Admin\Tabungan\TabunganRiwayat;
use App\Models\Kas;
use App\Models\Siswa;
use App\Models\Tabungan;
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

it('renders riwayat tabungan with dark mode classes and transactions list', function () {
    $siswa = Siswa::create([
        'nis' => '1008',
        'nama' => 'ATAYA IRTIZA ARSYA',
        'status' => 1,
        'lembaga_id' => 1,
        'jenis_kelamin' => 'l',
    ]);

    $kas = Kas::create([
        'nama' => 'Kas Tabungan Wajib',
        'lembaga_id' => 1,
        'saldo' => 40000,
        'tabungan' => true,
    ]);

    $tabungan = Tabungan::create([
        'siswa_id' => $siswa->id,
        'kas_id' => $kas->id,
        'saldo' => 40000,
    ]);

    $transaksi = $tabungan->transaksi()->create([
        'kode' => 'MTB1202609300086',
        'jumlah' => 40000,
        'keterangan' => 'Setoran Tabungan Wajib ATAYA IRTIZA ARSYA',
        'user_id' => $this->user->id,
    ]);

    $this->get(route('admin.tabungan.riwayat', $tabungan->id))
        ->assertOk()
        ->assertSee('Riwayat Tabungan: ATAYA IRTIZA ARSYA')
        ->assertSee('dark:text-gray-200', false)
        ->assertSee('dark:bg-gray-800', false)
        ->assertSee('dark:border-gray-700', false)
        ->assertSee('Kas Tabungan Wajib')
        ->assertSee('MTB1202609300086')
        ->assertSee('Setoran Tabungan Wajib ATAYA IRTIZA ARSYA')
        ->assertSee('Rp 40.000');

    Livewire::test(TabunganRiwayat::class, ['tabungan' => $tabungan])
        ->assertStatus(200)
        ->assertSee('ATAYA IRTIZA ARSYA')
        ->assertSee('MTB1202609300086');
});
