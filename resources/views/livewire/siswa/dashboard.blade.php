<div class="bg-gray-50 min-h-screen pb-6">
    <!-- Header Background (Gradient) -->
    <div class="bg-gradient-to-br from-emerald-700 via-emerald-600 to-teal-800 pt-12 pb-24 px-6 rounded-b-[48px] shadow-lg relative overflow-hidden">
        <!-- Abstract shapes for professional look -->
        <div class="absolute top-0 right-0 -mt-8 -mr-8 w-40 h-40 bg-white opacity-10 rounded-full blur-2xl"></div>
        <div class="absolute bottom-0 left-0 -mb-8 -ml-8 w-32 h-32 bg-white opacity-10 rounded-full blur-2xl"></div>
        
        <div class="relative z-10 flex justify-between items-start">
            <div class="flex items-center gap-4">
                <div class="bg-white/10 backdrop-blur-md border border-white/20 p-2.5 rounded-2xl flex items-center justify-center aspect-square h-14 w-14 shadow-inner">
                    <x-app-logo-icon class="size-8 object-contain drop-shadow-md text-white" />
                </div>
                <div>
                    <div class="text-xs text-emerald-100 font-medium tracking-widest uppercase mb-0.5">Selamat Datang Di</div>
                    <div class="font-bold text-lg leading-tight text-white drop-shadow-sm">{{ config('custom.lembaga.' . $siswa->lembaga_id, 'Sistem Akademik') }}</div>
                    <div class="text-[13px] text-emerald-50 mt-1 font-medium opacity-90 flex items-center gap-1.5">{{ $siswa->nama }}</div>
                </div>
            </div>
            <!-- Notification Bell -->
            <button class="bg-white/10 backdrop-blur-md border border-white/20 p-2 rounded-full text-white hover:bg-white/20 transition mt-1">
                <flux:icon name="bell" class="size-5" variant="outline" />
            </button>
        </div>
    </div>

    <!-- Saldo Card -->
    <div class="-mt-14 px-6 relative z-10">
        <div class="bg-white rounded-3xl p-6 shadow-xl shadow-emerald-900/5 border border-gray-100 flex flex-col justify-between overflow-hidden relative">
            <!-- Card Decoration -->
            <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-50 rounded-full blur-3xl opacity-80 -mr-10 -mt-10 pointer-events-none"></div>
            
            <div class="relative z-10 flex justify-between items-center mb-2">
                <div class="text-gray-500 text-sm font-semibold flex items-center gap-2">
                    <div class="bg-emerald-50 p-1.5 rounded-lg text-emerald-600">
                        <flux:icon name="wallet" class="size-4" variant="solid" />
                    </div>
                    Saldo Tabungan
                </div>
            </div>
            
            <div class="relative z-10 text-[34px] font-extrabold text-gray-800 mb-6 tracking-tight">
                Rp {{ number_format($saldo, 0, ',', '.') }}
            </div>
            
            <div class="relative z-10">
                <button class="w-full bg-emerald-600 hover:bg-emerald-700 transition text-white text-sm font-semibold px-4 py-3.5 rounded-2xl flex items-center justify-center gap-2 shadow-md shadow-emerald-600/20">
                    <flux:icon name="clock" class="size-5" variant="outline" />
                    Riwayat Setoran Tabungan
                </button>
            </div>
        </div>
    </div>

    <!-- Menu Grid -->
    <div class="px-6 mt-10">
        <h3 class="font-bold text-gray-800 mb-5 text-sm uppercase tracking-wider flex items-center gap-2">
            <span class="w-1.5 h-4 bg-emerald-500 rounded-full"></span>
            Menu Layanan
        </h3>
        <div class="grid grid-cols-4 gap-x-4 gap-y-6">
            <a href="#" class="flex flex-col items-center gap-2.5 group">
                <div class="w-14 h-14 bg-white border border-gray-100 rounded-[20px] flex items-center justify-center text-emerald-600 shadow-sm group-hover:shadow-md group-hover:border-emerald-200 group-hover:bg-emerald-50 transition-all duration-300">
                    <flux:icon name="document-text" class="size-6" variant="outline" />
                </div>
                <span class="text-[11px] text-gray-600 font-semibold group-hover:text-emerald-700 transition-colors">Tagihan</span>
            </a>
            <a href="#" class="flex flex-col items-center gap-2.5 group">
                <div class="w-14 h-14 bg-white border border-gray-100 rounded-[20px] flex items-center justify-center text-emerald-600 shadow-sm group-hover:shadow-md group-hover:border-emerald-200 group-hover:bg-emerald-50 transition-all duration-300">
                    <flux:icon name="wallet" class="size-6" variant="outline" />
                </div>
                <span class="text-[11px] text-gray-600 font-semibold group-hover:text-emerald-700 transition-colors">Tabungan</span>
            </a>
            <a href="#" class="flex flex-col items-center gap-2.5 group">
                <div class="w-14 h-14 bg-white border border-gray-100 rounded-[20px] flex items-center justify-center text-emerald-600 shadow-sm group-hover:shadow-md group-hover:border-emerald-200 group-hover:bg-emerald-50 transition-all duration-300">
                    <flux:icon name="arrows-right-left" class="size-6" variant="outline" />
                </div>
                <span class="text-[11px] text-gray-600 font-semibold group-hover:text-emerald-700 transition-colors">Transaksi</span>
            </a>
            <a href="#" class="flex flex-col items-center gap-2.5 group">
                <div class="w-14 h-14 bg-white border border-gray-100 rounded-[20px] flex items-center justify-center text-emerald-600 shadow-sm group-hover:shadow-md group-hover:border-emerald-200 group-hover:bg-emerald-50 transition-all duration-300">
                    <flux:icon name="users" class="size-6" variant="outline" />
                </div>
                <span class="text-[11px] text-gray-600 font-semibold group-hover:text-emerald-700 transition-colors">Absensi</span>
            </a>
            <a href="#" class="flex flex-col items-center gap-2.5 group">
                <div class="w-14 h-14 bg-white border border-gray-100 rounded-[20px] flex items-center justify-center text-emerald-600 shadow-sm group-hover:shadow-md group-hover:border-emerald-200 group-hover:bg-emerald-50 transition-all duration-300">
                    <flux:icon name="credit-card" class="size-6" variant="outline" />
                </div>
                <span class="text-[11px] text-gray-600 font-semibold group-hover:text-emerald-700 transition-colors">Kartu</span>
            </a>
            <a href="{{ route('siswa.profil') }}" class="flex flex-col items-center gap-2.5 group">
                <div class="w-14 h-14 bg-white border border-gray-100 rounded-[20px] flex items-center justify-center text-emerald-600 shadow-sm group-hover:shadow-md group-hover:border-emerald-200 group-hover:bg-emerald-50 transition-all duration-300">
                    <flux:icon name="user" class="size-6" variant="outline" />
                </div>
                <span class="text-[11px] text-gray-600 font-semibold group-hover:text-emerald-700 transition-colors">Akun</span>
            </a>
            <a href="#" class="flex flex-col items-center gap-2.5 group">
                <div class="w-14 h-14 bg-white border border-gray-100 rounded-[20px] flex items-center justify-center text-emerald-600 shadow-sm group-hover:shadow-md group-hover:border-emerald-200 group-hover:bg-emerald-50 transition-all duration-300">
                    <flux:icon name="squares-2x2" class="size-6" variant="outline" />
                </div>
                <span class="text-[11px] text-gray-600 font-semibold group-hover:text-emerald-700 transition-colors">Lainnya</span>
            </a>
        </div>
    </div>

    <!-- Info dan Berita -->
    <div class="px-6 mt-10 mb-8">
        <div class="flex justify-between items-center mb-5">
            <h3 class="font-bold text-gray-800 text-sm uppercase tracking-wider flex items-center gap-2">
                <span class="w-1.5 h-4 bg-emerald-500 rounded-full"></span>
                Info & Berita
            </h3>
            <a href="{{ route('siswa.informasi') }}" class="text-emerald-600 text-[12px] font-semibold hover:text-emerald-700 transition flex items-center">
                Lihat Semua <flux:icon name="chevron-right" class="size-3.5 ml-0.5" />
            </a>
        </div>
        
        <!-- Modern News Banner -->
        <div class="bg-gray-900 rounded-[24px] p-6 text-white relative overflow-hidden mb-5 shadow-lg shadow-gray-900/10">
            <div class="absolute right-0 top-0 w-40 h-40 bg-emerald-500 rounded-full blur-[60px] opacity-20 -mr-10 -mt-10 pointer-events-none"></div>
            <div class="absolute left-0 bottom-0 w-32 h-32 bg-emerald-600 rounded-full blur-[50px] opacity-20 -ml-10 -mb-10 pointer-events-none"></div>
            <div class="relative z-10">
                <div class="inline-block bg-emerald-500/20 text-emerald-400 text-[10px] font-bold px-2.5 py-1 rounded-md mb-3 border border-emerald-500/20 tracking-wide">INFORMASI</div>
                <div class="font-bold text-[17px] mb-5 leading-snug text-white/95">Pembaruan Sistem Akademik & Pembayaran</div>
                <button class="bg-emerald-500 hover:bg-emerald-600 text-white font-semibold px-6 py-2.5 rounded-xl text-xs transition shadow-md shadow-emerald-500/20 w-full flex items-center justify-center gap-2">
                    Baca Selengkapnya
                    <flux:icon name="arrow-right" class="size-3.5" />
                </button>
            </div>
        </div>
        
        <div class="space-y-4">
            <!-- Elegant News Item 1 -->
            <a href="#" class="block bg-white rounded-[20px] p-5 shadow-sm border border-gray-100 hover:border-emerald-200 hover:shadow-md transition-all duration-300 group">
                <div class="flex items-center gap-2 mb-2.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span class="text-[11px] text-gray-500 font-semibold tracking-wide">02-08-2026</span>
                </div>
                <div class="font-bold text-[15px] text-gray-800 mb-2 group-hover:text-emerald-700 transition-colors">Tagihan Bulan Juli 2026</div>
                <div class="text-[13px] text-gray-500 leading-relaxed line-clamp-2">Assalamu'alaikum. Wr. Wb. Sugeng sonten Bapak/Ibu. Pangapunten wonten kesalahan input tagihan untuk SMAI Bulan Juli 2026, Seharusnya gratis saking Yayasan.</div>
            </a>
            
            <!-- Elegant News Item 2 -->
            <a href="#" class="block bg-white rounded-[20px] p-5 shadow-sm border border-gray-100 hover:border-emerald-200 hover:shadow-md transition-all duration-300 group">
                <div class="flex items-center gap-2 mb-2.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span class="text-[11px] text-gray-500 font-semibold tracking-wide">10-07-2026</span>
                </div>
                <div class="font-bold text-[15px] text-gray-800 mb-2 group-hover:text-emerald-700 transition-colors">Siap Berangkat Mondok !</div>
                <div class="text-[13px] text-gray-500 leading-relaxed line-clamp-2">Pemberitahuan kepada seluruh santri untuk mempersiapkan segala perlengkapan sebelum keberangkatan.</div>
            </a>
        </div>
    </div>
</div>
