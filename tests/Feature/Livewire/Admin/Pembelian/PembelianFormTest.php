<?php

use App\Livewire\Admin\Pembelian\PembelianForm;
use App\Models\Barang;
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

it('renders the pembelian form component', function () {
    Livewire::test(PembelianForm::class)
        ->assertStatus(200)
        ->assertSee('Detail Barang')
        ->assertSee('Kode Pembelian');
});

it('can automatically create a new barang if not found', function () {
    $component = Livewire::test(PembelianForm::class)
        ->call('quickCreateBarang', 'Buku Tulis Sinar Dunia 58 Lembar', 0);

    $this->assertDatabaseHas('barang', [
        'nama' => 'Buku Tulis Sinar Dunia 58 Lembar',
        'satuan' => 'PCS',
    ]);

    $created = Barang::where('nama', 'Buku Tulis Sinar Dunia 58 Lembar')->first();
    expect($created)->not->toBeNull();
    expect($component->get('items.0.barang_id'))->toBe($created->id);
});

it('reuses existing barang if name already exists during quick creation', function () {
    $existing = Barang::create([
        'nama' => 'Kertas HVS A4 70gr',
        'kode' => 'BRG-HVS01',
        'jenis' => 'UMU',
        'satuan' => 'RIM',
        'harga_beli' => 45000,
        'harga' => 50000,
        'stok' => 10,
        'stok_minimal' => 2,
        'lembaga_id' => 1,
    ]);

    $component = Livewire::test(PembelianForm::class)
        ->call('quickCreateBarang', 'Kertas HVS A4 70gr', 0);

    expect($component->get('items.0.barang_id'))->toBe($existing->id);
    expect((int) $component->get('items.0.harga'))->toBe(45000);
});

it('can save a purchase with multiple items and update barang harga_beli', function () {
    $supplier = Supplier::create([
        'nama' => 'PT Toko Buku Sentosa',
        'hp' => '081234567890',
    ]);

    $barang = Barang::create([
        'nama' => 'Pulpen Standard AE7',
        'kode' => 'BRG-AE7',
        'jenis' => 'UMU',
        'satuan' => 'PCS',
        'harga_beli' => 2000,
        'harga' => 3000,
        'stok' => 5,
        'stok_minimal' => 1,
        'lembaga_id' => 1,
    ]);

    Livewire::test(PembelianForm::class)
        ->set('kode', 'PB-TEST01')
        ->set('supplier_id', $supplier->id)
        ->set('items.0.barang_id', $barang->id)
        ->set('items.0.jumlah', 10)
        ->set('items.0.harga', 2500)
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('admin.pembelian.index'));

    $this->assertDatabaseHas('pembelian', [
        'kode' => 'PB-TEST01',
        'supplier_id' => $supplier->id,
        'total' => 25000,
    ]);

    $this->assertDatabaseHas('detail_pembelian', [
        'barang_id' => $barang->id,
        'jumlah' => 10,
        'harga' => 2500,
        'total' => 25000,
    ]);

    $barang->refresh();
    expect((int) $barang->harga_beli)->toBe(2500);
});

it('correctly calculates total and records purchase when items payload is passed from alpine', function () {
    $supplier = Supplier::create([
        'nama' => 'Supplier Mandiri',
        'hp' => '08999999999',
    ]);

    $barang1 = Barang::create([
        'nama' => 'Pensil 2B Faber Castell',
        'kode' => 'BRG-2B',
        'jenis' => 'UMU',
        'satuan' => 'PCS',
        'harga_beli' => 3000,
        'harga' => 4500,
        'stok' => 20,
        'stok_minimal' => 5,
        'lembaga_id' => 1,
    ]);

    $payload = [
        [
            'barang_id' => $barang1->id,
            'jumlah' => 10,
            'harga' => 3500,
            'total' => 35000,
        ],
    ];

    Livewire::test(PembelianForm::class)
        ->set('kode', 'PB-PAYLOAD01')
        ->set('supplier_id', $supplier->id)
        ->call('save', $payload)
        ->assertHasNoErrors()
        ->assertRedirect(route('admin.pembelian.index'));

    $this->assertDatabaseHas('pembelian', [
        'kode' => 'PB-PAYLOAD01',
        'total' => 35000,
    ]);

    $this->assertDatabaseHas('detail_pembelian', [
        'barang_id' => $barang1->id,
        'jumlah' => 10,
        'harga' => 3500,
        'total' => 35000,
    ]);
});

it('can create a new barang without index and then save purchase with that barang', function () {
    $supplier = Supplier::create([
        'nama' => 'CV Sumber Rejeki',
        'hp' => '08123123123',
    ]);

    $test = Livewire::test(PembelianForm::class)
        ->set('supplier_id', $supplier->id);

    // Frontend calls quickCreateBarang without passing index
    $test->call('quickCreateBarang', 'Spidol Permanen Snowman Hitam');

    $newBarang = Barang::where('nama', 'Spidol Permanen Snowman Hitam')->first();
    expect($newBarang)->not->toBeNull();
    $newId = (string) $newBarang->id;

    // Frontend then submits form with payload
    $payload = [
        [
            'id' => null,
            'barang_id' => $newId,
            'jumlah' => 5,
            'harga' => 8500,
            'total' => 42500,
        ],
    ];

    $test->call('save', $payload)
        ->assertHasNoErrors()
        ->assertRedirect(route('admin.pembelian.index'));

    $this->assertDatabaseHas('pembelian', [
        'supplier_id' => $supplier->id,
        'total' => 42500,
    ]);

    $this->assertDatabaseHas('detail_pembelian', [
        'barang_id' => $newId,
        'jumlah' => 5,
        'harga' => 8500,
        'total' => 42500,
    ]);
});

it('filters out empty barang rows on save and completes the purchase', function () {
    $supplier = Supplier::create([
        'nama' => 'CV Makmur Bersama',
        'hp' => '08987654321',
    ]);

    $barang = Barang::create([
        'nama' => 'Penghapus Boxy Hitam',
        'kode' => 'BRG-BOXY',
        'jenis' => 'UMU',
        'satuan' => 'PCS',
        'harga_beli' => 4000,
        'harga' => 6000,
        'stok' => 50,
        'stok_minimal' => 5,
        'lembaga_id' => 1,
    ]);

    $payloadWithEmptyRow = [
        [
            'id' => null,
            'barang_id' => (string) $barang->id,
            'jumlah' => 3,
            'harga' => 4500,
            'total' => 13500,
        ],
        [
            'id' => null,
            'barang_id' => '',
            'jumlah' => 1,
            'harga' => 0,
            'total' => 0,
        ],
    ];

    Livewire::test(PembelianForm::class)
        ->set('supplier_id', $supplier->id)
        ->call('save', $payloadWithEmptyRow)
        ->assertHasNoErrors()
        ->assertRedirect(route('admin.pembelian.index'));

    $this->assertDatabaseHas('pembelian', [
        'supplier_id' => $supplier->id,
        'total' => 13500,
    ]);

    $this->assertDatabaseHas('detail_pembelian', [
        'barang_id' => $barang->id,
        'jumlah' => 3,
        'harga' => 4500,
        'total' => 13500,
    ]);
});
