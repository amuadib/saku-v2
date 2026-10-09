<?php

use App\Http\Controllers\PrintController;
use App\Livewire\Admin\Barang\BarangForm;
use App\Livewire\Admin\Barang\BarangList;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Kas\KasForm;
use App\Livewire\Admin\Kas\KasList;
use App\Livewire\Admin\Kelas\KelasForm;
use App\Livewire\Admin\Kelas\KelasList;
use App\Livewire\Admin\LogAktivitas\LogAktivitasList;
use App\Livewire\Admin\Pembelian\PembelianForm;
use App\Livewire\Admin\Pembelian\PembelianList;
use App\Livewire\Admin\Pengaduan\PengaduanForm;
use App\Livewire\Admin\Pengaduan\PengaduanList;
use App\Livewire\Admin\Penjualan\PenjualanForm;
use App\Livewire\Admin\Penjualan\PenjualanList;
use App\Livewire\Admin\Periode\PeriodeForm;
use App\Livewire\Admin\Periode\PeriodeList;
use App\Livewire\Admin\Siswa\SiswaForm;
use App\Livewire\Admin\Siswa\SiswaList;
use App\Livewire\Admin\Siswa\SiswaShow;
use App\Livewire\Admin\Supplier\SupplierForm;
use App\Livewire\Admin\Supplier\SupplierList;
use App\Livewire\Admin\Tabungan\TabunganForm;
use App\Livewire\Admin\Tabungan\TabunganList;
use App\Livewire\Admin\Tagihan\TagihanForm;
use App\Livewire\Admin\Tagihan\TagihanList;
use App\Livewire\Admin\Transaksi\TransaksiForm;
use App\Livewire\Admin\Transaksi\TransaksiList;
use App\Livewire\Admin\User\UserForm;
use App\Livewire\Admin\User\UserList;
use App\Livewire\Siswa\Informasi;
use App\Livewire\Siswa\Profil;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::get('/login/magic/{user}', function (User $user) {
    if (! request()->hasValidSignature(false)) {
        abort(401, 'Link tidak valid atau sudah kadaluarsa.');
    }
    auth()->login($user);

    return redirect('/dashboard');
})->name('login.magic');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::redirect('/dashboard', '/admin')->name('dashboard');

    // Route khusus Siswa
    Route::get('/informasi', Informasi::class)->name('siswa.informasi');
    Route::get('/profil', Profil::class)->name('siswa.profil');
});

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard');

    // Barang CRUD
    Route::get('/barang', BarangList::class)->name('barang.index');
    Route::get('/barang/create', BarangForm::class)->name('barang.create');
    Route::get('/barang/{barang}/edit', BarangForm::class)->name('barang.edit');

    // Kas CRUD
    Route::get('/kas', KasList::class)->name('kas.index');
    Route::get('/kas/create', KasForm::class)->name('kas.create');
    Route::get('/kas/{kas}/edit', KasForm::class)->name('kas.edit');

    // Kelas CRUD
    Route::get('/kelas', KelasList::class)->name('kelas.index');
    Route::get('/kelas/create', KelasForm::class)->name('kelas.create');
    Route::get('/kelas/{kelas}/edit', KelasForm::class)->name('kelas.edit');

    // Periode CRUD
    Route::get('/periode', PeriodeList::class)->name('periode.index');
    Route::get('/periode/create', PeriodeForm::class)->name('periode.create');
    Route::get('/periode/{periode}/edit', PeriodeForm::class)->name('periode.edit');

    // Siswa CRUD
    Route::get('/siswa', SiswaList::class)->name('siswa.index');
    Route::get('/siswa/create', SiswaForm::class)->name('siswa.create');
    Route::get('/siswa/{siswa}/edit', SiswaForm::class)->name('siswa.edit');
    Route::get('/siswa/{siswa}', SiswaShow::class)->name('siswa.show');
    Route::get('/siswa/{siswa}/cetak-tagihan', [PrintController::class, 'cetakTagihan'])->name('siswa.cetak-tagihan');
    Route::get('/siswa/{siswa}/cetak-kwitansi', [PrintController::class, 'cetakKwitansi'])->name('siswa.cetak-kwitansi');
    Route::get('/siswa/{siswa}/cetak-transaksi', [PrintController::class, 'cetakTransaksi'])->name('siswa.cetak-transaksi');

    // User CRUD
    Route::get('/user', UserList::class)->name('user.index');
    Route::get('/user/create', UserForm::class)->name('user.create');
    Route::get('/user/{user}/edit', UserForm::class)->name('user.edit');
    Route::get('/user/{user}/impersonate', function (User $user) {
        if (! auth()->user()->can('impersonate', $user)) {
            abort(403);
        }
        session()->put('impersonate_by', auth()->id());
        auth()->login($user);

        return redirect()->route('admin.dashboard')->with('message', 'Berhasil login sebagai '.$user->username);
    })->name('user.impersonate');

    Route::get('/leave-impersonate', function () {
        if (session()->has('impersonate_by')) {
            $original = User::find(session()->pull('impersonate_by'));
            if ($original) {
                auth()->login($original);
            }

            return redirect()->route('admin.user.index')->with('message', 'Kembali ke akun semula.');
        }

        return redirect()->route('admin.dashboard');
    })->name('leave-impersonate');

    // Supplier CRUD
    Route::get('/supplier', SupplierList::class)->name('supplier.index');
    Route::get('/supplier/create', SupplierForm::class)->name('supplier.create');
    Route::get('/supplier/{supplier}/edit', SupplierForm::class)->name('supplier.edit');

    // Tabungan CRUD
    Route::get('/tabungan', TabunganList::class)->name('tabungan.index');
    Route::get('/tabungan/create', TabunganForm::class)->name('tabungan.create');
    Route::get('/tabungan/{tabungan}/edit', TabunganForm::class)->name('tabungan.edit');

    // Tagihan CRUD
    Route::get('/tagihan', TagihanList::class)->name('tagihan.index');
    Route::get('/tagihan/create', TagihanForm::class)->name('tagihan.create');
    Route::get('/tagihan/{tagihan}/edit', TagihanForm::class)->name('tagihan.edit');

    // Pembelian CRUD
    Route::get('/pembelian', PembelianList::class)->name('pembelian.index');
    Route::get('/pembelian/create', PembelianForm::class)->name('pembelian.create');
    Route::get('/pembelian/{pembelian}/edit', PembelianForm::class)->name('pembelian.edit');

    // Pengaduan CRUD
    Route::get('/pengaduan', PengaduanList::class)->name('pengaduan.index');
    Route::get('/pengaduan/create', PengaduanForm::class)->name('pengaduan.create');
    Route::get('/pengaduan/{pengaduan}/edit', PengaduanForm::class)->name('pengaduan.edit');

    // Penjualan CRUD
    Route::get('/penjualan', PenjualanList::class)->name('penjualan.index');
    Route::get('/penjualan/create', PenjualanForm::class)->name('penjualan.create');
    Route::get('/penjualan/{penjualan}/edit', PenjualanForm::class)->name('penjualan.edit');

    // Transaksi (Buku Besar) - Read Only List for now
    Route::get('/transaksi', TransaksiList::class)->name('transaksi.index');
    Route::get('/transaksi/create', TransaksiForm::class)->name('transaksi.create');

    // Log Aktivitas
    Route::get('/log-aktivitas', LogAktivitasList::class)->name('log-aktivitas.index');
});

require __DIR__.'/settings.php';
