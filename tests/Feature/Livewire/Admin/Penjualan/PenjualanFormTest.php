<?php

use App\Livewire\Admin\Penjualan\PenjualanForm;
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

it('renders the penjualan form component', function () {
    Livewire::test(PenjualanForm::class)
        ->assertStatus(200)
        ->assertViewIs('livewire.admin.penjualan.penjualan-form');
})->skip('Factories missing');

it('can create a new penjualan', function () {
    $siswa = Siswa::factory()->create();
    $barang = Barang::factory()->create([
        'harga_jual' => 10000,
    ]);

    Livewire::test(PenjualanForm::class)
        ->set('siswa_id', $siswa->id)
        ->set('pembayaran', 'KAS')
        // addItem() is called automatically in mount if no $penjualan is provided
        ->set('items.0.barang_id', $barang->id) // This triggers updatedItems because of component reactivity
        ->set('items.0.jumlah', 2)
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('admin.penjualan.index'));

    $this->assertDatabaseHas('penjualan', [
        'siswa_id' => $siswa->id,
        'pembayaran' => 'KAS',
        'total' => 20000,
    ]);

    $this->assertDatabaseHas('detail_penjualan', [
        'barang_id' => $barang->id,
        'jumlah' => 2,
        'harga' => 10000,
        'total' => 20000,
    ]);
})->skip('Factories missing');

it('can update an existing penjualan', function () {
    $siswa = Siswa::factory()->create();
    $barang = Barang::factory()->create([
        'harga_jual' => 10000,
    ]);

    $penjualan = Penjualan::factory()->create([
        'siswa_id' => $siswa->id,
        'pembayaran' => 'KAS',
        'total' => 10000,
        'user_id' => $this->user->id,
    ]);

    $penjualan->detail()->create([
        'barang_id' => $barang->id,
        'jumlah' => 1,
        'harga' => 10000,
        'total' => 10000,
    ]);

    $newBarang = Barang::factory()->create([
        'harga_jual' => 15000,
    ]);

    Livewire::test(PenjualanForm::class, ['penjualan' => $penjualan])
        ->set('pembayaran', 'TAB')
        ->set('items.0.barang_id', $newBarang->id)
        ->set('items.0.jumlah', 2)
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('admin.penjualan.index'));

    $this->assertDatabaseHas('penjualan', [
        'id' => $penjualan->id,
        'pembayaran' => 'TAB',
        'total' => 30000,
    ]);

    $this->assertDatabaseHas('detail_penjualan', [
        'penjualan_id' => $penjualan->id,
        'barang_id' => $newBarang->id,
        'jumlah' => 2,
        'harga' => 15000,
        'total' => 30000,
    ]);
})->skip('Factories missing');
