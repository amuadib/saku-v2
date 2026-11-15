<?php

use App\Livewire\Admin\Pembelian\PembelianList;
use App\Models\Barang;
use App\Models\Pembelian;
use App\Models\Supplier;
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

it('renders the pembelian list component and displays purchase details', function () {
    $supplier = Supplier::create([
        'nama' => 'PT Harapan Indah',
        'hp' => '081234567890',
    ]);

    $barang = Barang::create([
        'nama' => 'Buku Gambar A3 Kiky',
        'kode' => 'BRG-A3KIKY',
        'jenis' => 'UMU',
        'satuan' => 'PCS',
        'harga_beli' => 7500,
        'harga' => 10000,
        'stok' => 20,
        'stok_minimal' => 2,
        'lembaga_id' => 1,
    ]);

    $pembelian = Pembelian::create([
        'kode' => 'PB-LIST01',
        'supplier_id' => $supplier->id,
        'total' => 75000,
        'user_id' => $this->user->id,
    ]);

    $pembelian->detail()->create([
        'barang_id' => $barang->id,
        'jumlah' => 10,
        'harga' => 7500,
        'total' => 75000,
    ]);

    Livewire::test(PembelianList::class)
        ->assertStatus(200)
        ->assertSee('Detail Barang')
        ->assertSee('Kode / Tanggal')
        ->assertSee('PB-LIST01')
        ->assertSee('PT Harapan Indah')
        ->assertSee('Buku Gambar A3 Kiky')
        ->assertSee('10 PCS')
        ->assertSee('Rp 75.000')
        ->assertDontSee('admin.pembelian.edit');
});

it('can search purchases by item name in detail', function () {
    $supplier = Supplier::create([
        'nama' => 'Toko Alat Tulis Kita',
        'hp' => '0877777777',
    ]);

    $barang1 = Barang::create([
        'nama' => 'Krayon Pascola 24 Warna',
        'kode' => 'BRG-KRY24',
        'jenis' => 'UMU',
        'satuan' => 'SET',
        'harga_beli' => 25000,
        'harga' => 30000,
        'stok' => 15,
        'stok_minimal' => 3,
        'lembaga_id' => 1,
    ]);

    $pembelian = Pembelian::create([
        'kode' => 'PB-PASCOLA',
        'supplier_id' => $supplier->id,
        'total' => 50000,
        'user_id' => $this->user->id,
    ]);

    $pembelian->detail()->create([
        'barang_id' => $barang1->id,
        'jumlah' => 2,
        'harga' => 25000,
        'total' => 50000,
    ]);

    Livewire::test(PembelianList::class)
        ->set('search', 'Pascola')
        ->assertSee('PB-PASCOLA')
        ->assertSee('Krayon Pascola 24 Warna')
        ->set('search', 'ProdukYangTidakAda999')
        ->assertDontSee('PB-PASCOLA');
});

it('can delete a purchase transaction along with its details', function () {
    $supplier = Supplier::create([
        'nama' => 'Supplier Cemerlang',
        'hp' => '085555555',
    ]);

    $barang = Barang::create([
        'nama' => 'Stapler Max HD-10',
        'kode' => 'BRG-STPL',
        'jenis' => 'UMU',
        'satuan' => 'PCS',
        'harga_beli' => 15000,
        'harga' => 20000,
        'stok' => 10,
        'stok_minimal' => 2,
        'lembaga_id' => 1,
    ]);

    $pembelian = Pembelian::create([
        'kode' => 'PB-DELETE01',
        'supplier_id' => $supplier->id,
        'total' => 30000,
        'user_id' => $this->user->id,
    ]);

    $detail = $pembelian->detail()->create([
        'barang_id' => $barang->id,
        'jumlah' => 2,
        'harga' => 15000,
        'total' => 30000,
    ]);

    Livewire::test(PembelianList::class)
        ->call('delete', $pembelian->id)
        ->assertStatus(200);

    $this->assertDatabaseMissing('pembelian', ['id' => $pembelian->id]);
    $this->assertDatabaseMissing('detail_pembelian', ['id' => $detail->id]);
});
