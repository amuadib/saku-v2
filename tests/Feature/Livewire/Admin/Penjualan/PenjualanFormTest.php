<?php

use App\Livewire\Admin\Penjualan\PenjualanForm;
use App\Models\Barang;
use App\Models\Kas;
use App\Models\Penjualan;
use App\Models\Siswa;
use App\Models\Tabungan;
use App\Models\Tagihan;
use App\Models\Transaksi;
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

it('renders admin penjualan create page with tom select pos data', function () {
    $siswa = Siswa::create([
        'nis' => '2026001',
        'nama' => 'Siti Aminah',
        'status' => 1,
        'lembaga_id' => 1,
        'jenis_kelamin' => 'p',
    ]);

    $barang = Barang::forceCreate([
        'kode' => 'BRG-001',
        'nama' => 'Buku Tulis Sinar',
        'lembaga_id' => 1,
        'harga' => 5000,
        'stok' => 25,
        'satuan' => 'PCS',
    ]);

    $this->get(route('admin.penjualan.create'))
        ->assertOk()
        ->assertSee('createPenjualanPos')
        ->assertSee('Siti Aminah')
        ->assertSee('Buku Tulis Sinar')
        ->assertSee('BRG-001');
});

it('can create tunai penjualan via single-roundtrip saveTransaction', function () {
    $siswa = Siswa::create([
        'nis' => '2026002',
        'nama' => 'Ahmad Dahlan',
        'status' => 1,
        'lembaga_id' => 1,
        'jenis_kelamin' => 'l',
    ]);

    $barang = Barang::forceCreate([
        'kode' => 'BRG-002',
        'nama' => 'Pulpen Gel',
        'lembaga_id' => 1,
        'harga' => 3500,
        'stok' => 20,
        'satuan' => 'PCS',
    ]);

    $kas = Kas::create([
        'nama' => 'Kas Penjualan',
        'lembaga_id' => 1,
        'saldo' => 100000,
        'penjualan' => true,
    ]);

    Livewire::test(PenjualanForm::class)
        ->call('saveTransaction', [
            'kode' => 'PJ-FAST01',
            'siswa_id' => $siswa->id,
            'pembayaran' => 'tun',
            'items' => [
                [
                    'barang_id' => $barang->id,
                    'jumlah' => 4,
                    'harga' => 3500,
                ],
            ],
        ])
        ->assertHasNoErrors()
        ->assertRedirect(route('admin.penjualan.index'));

    $penjualan = Penjualan::where('kode', 'PJ-FAST01')->first();
    expect($penjualan)->not->toBeNull();
    expect((int) $penjualan->total)->toBe(14000);
    expect($penjualan->pembayaran)->toBe('tun');

    $this->assertDatabaseHas('detail_penjualan', [
        'penjualan_id' => $penjualan->id,
        'barang_id' => $barang->id,
        'jumlah' => 4,
        'harga' => 3500,
        'total' => 14000,
    ]);

    expect($barang->fresh()->stok)->toBe(16);
    expect((int) $kas->fresh()->saldo)->toBe(114000);
    expect(Transaksi::where('transable_id', $penjualan->id)->where('transable_type', Penjualan::class)->exists())->toBeTrue();
});

it('validates stock limits across items in saveTransaction without corrupting stock', function () {
    $siswa = Siswa::create([
        'nis' => '2026003',
        'nama' => 'Budi Santoso',
        'status' => 1,
        'lembaga_id' => 1,
        'jenis_kelamin' => 'l',
    ]);

    $barang = Barang::forceCreate([
        'kode' => 'BRG-003',
        'nama' => 'Penggaris',
        'lembaga_id' => 1,
        'harga' => 2000,
        'stok' => 3,
        'satuan' => 'PCS',
    ]);

    Kas::create([
        'nama' => 'Kas Penjualan',
        'lembaga_id' => 1,
        'saldo' => 0,
        'penjualan' => true,
    ]);

    Livewire::test(PenjualanForm::class)
        ->call('saveTransaction', [
            'kode' => 'PJ-OVER01',
            'siswa_id' => $siswa->id,
            'pembayaran' => 'tun',
            'items' => [
                [
                    'barang_id' => $barang->id,
                    'jumlah' => 2,
                    'harga' => 2000,
                ],
                [
                    'barang_id' => $barang->id,
                    'jumlah' => 2,
                    'harga' => 2000,
                ],
            ],
        ])
        ->assertHasErrors('items')
        ->assertNoRedirect();

    expect($barang->fresh()->stok)->toBe(3);
    expect(Penjualan::where('kode', 'PJ-OVER01')->exists())->toBeFalse();
});

it('handles tabungan balance validation and tagihan creation via saveTransaction', function () {
    $siswa = Siswa::create([
        'nis' => '2026004',
        'nama' => 'Citra Lestari',
        'status' => 1,
        'lembaga_id' => 1,
        'jenis_kelamin' => 'p',
    ]);

    $barang = Barang::forceCreate([
        'kode' => 'BRG-004',
        'nama' => 'Seragam Olahraga',
        'lembaga_id' => 1,
        'harga' => 50000,
        'stok' => 10,
        'satuan' => 'PCS',
    ]);

    $kas = Kas::create([
        'nama' => 'Kas Penjualan',
        'lembaga_id' => 1,
        'saldo' => 0,
        'penjualan' => true,
    ]);

    $tabungan = Tabungan::create([
        'siswa_id' => $siswa->id,
        'kas_id' => $kas->id,
        'saldo' => 30000,
    ]);

    Livewire::test(PenjualanForm::class)
        ->call('saveTransaction', [
            'kode' => 'PJ-TAB01',
            'siswa_id' => $siswa->id,
            'pembayaran' => 'tab',
            'items' => [
                ['barang_id' => $barang->id, 'jumlah' => 1, 'harga' => 50000],
            ],
        ])
        ->assertHasErrors('pembayaran')
        ->assertNoRedirect();

    $tabungan->update(['saldo' => 75000]);

    Livewire::test(PenjualanForm::class)
        ->call('saveTransaction', [
            'kode' => 'PJ-TAB02',
            'siswa_id' => $siswa->id,
            'pembayaran' => 'tab',
            'items' => [
                ['barang_id' => $barang->id, 'jumlah' => 1, 'harga' => 50000],
            ],
        ])
        ->assertHasNoErrors()
        ->assertRedirect(route('admin.penjualan.index'));

    expect((int) $tabungan->fresh()->saldo)->toBe(25000);
    expect((int) $kas->fresh()->saldo)->toBe(50000);

    Livewire::test(PenjualanForm::class)
        ->call('saveTransaction', [
            'kode' => 'PJ-TAG01',
            'siswa_id' => $siswa->id,
            'pembayaran' => 'tag',
            'items' => [
                ['barang_id' => $barang->id, 'jumlah' => 1, 'harga' => 50000],
            ],
        ])
        ->assertHasNoErrors()
        ->assertRedirect(route('admin.penjualan.index'));

    $penjualanTag = Penjualan::where('kode', 'PJ-TAG01')->first();
    expect(Tagihan::where('tagihanable_id', $penjualanTag->id)->where('tagihanable_type', Penjualan::class)->exists())->toBeTrue();
});
