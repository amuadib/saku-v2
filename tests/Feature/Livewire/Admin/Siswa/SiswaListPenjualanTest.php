<?php

use App\Livewire\Admin\Siswa\SiswaPenjualanModal;
use App\Models\Barang;
use App\Models\Penjualan;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('can open penjualan modal from siswa list', function () {
    $siswa = Siswa::factory()->create(['nama' => 'Budi Tabungan']);

    Livewire::test(SiswaPenjualanModal::class)
        ->dispatch('open-penjualan-modal', siswa_id: $siswa->id)
        ->assertSet('showPenjualanModal', true)
        ->assertSet('penjualan_siswa_id', $siswa->id)
        ->assertSet('penjualan_siswa_nama', 'Budi Tabungan')
        ->assertSet('penjualan_pembayaran', 'tun');
})->skip('Factories missing');

it('can save a new penjualan from siswa list', function () {
    $siswa = Siswa::factory()->create();
    $barang = Barang::factory()->create([
        'harga' => 10000,
        'stok' => 50,
    ]);

    Livewire::test(SiswaPenjualanModal::class)
        ->dispatch('open-penjualan-modal', siswa_id: $siswa->id)
        ->set('penjualan_items.0.barang_id', $barang->id)
        ->set('penjualan_items.0.jumlah', 2)
        // Simulate Livewire's updated hook for calculation
        ->call('calculatePenjualanTotal')
        ->call('savePenjualan')
        ->assertHasNoErrors()
        ->assertSet('showPenjualanModal', false);

    $this->assertDatabaseHas('penjualan', [
        'siswa_id' => $siswa->id,
        'pembayaran' => 'tun',
    ]);

    $penjualan = Penjualan::where('siswa_id', $siswa->id)->first();

    $this->assertDatabaseHas('detail_penjualan', [
        'penjualan_id' => $penjualan->id,
        'barang_id' => $barang->id,
        'jumlah' => 2,
    ]);
})->skip('Factories missing');
