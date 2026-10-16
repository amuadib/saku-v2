<div class="bg-gray-50 min-h-screen pb-6">
    <!-- Header Background (Gradient) -->
    <div class="bg-gradient-to-br from-emerald-700 via-emerald-600 to-teal-800 pt-10 pb-22 px-6 rounded-b-[44px] shadow-lg relative overflow-hidden">
        <!-- Abstract shapes for professional look -->
        <div class="absolute top-0 right-0 -mt-8 -mr-8 w-40 h-40 bg-white opacity-10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 -mb-8 -ml-8 w-32 h-32 bg-white opacity-10 rounded-full blur-2xl pointer-events-none"></div>
        
        <!-- Baris Atas: Lembaga & Nama Aplikasi Kecil -->
        <div class="relative z-10 flex justify-between items-center mb-4">
            <div class="flex items-center gap-2 min-w-0 flex-1 mr-3">
                <div class="size-7 rounded-lg bg-white/15 backdrop-blur-md border border-white/20 p-0.5 flex items-center justify-center shrink-0 overflow-hidden" style="width: 28px; height: 28px; min-width: 28px; min-height: 28px;">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="Logo" class="size-full object-contain" style="width: 100%; height: 100%; max-width: 28px; max-height: 28px; object-fit: contain;" />
                    @else
                        <x-app-logo-icon class="size-4 text-white" />
                    @endif
                </div>
                <div class="min-w-0 flex items-center gap-1.5 truncate">
                    <span class="text-xs font-semibold text-white truncate drop-shadow-xs">{{ $lembaga['nama'] ?? 'Sistem Akademik' }}</span>
                    <span class="text-[9px] font-bold text-emerald-200 bg-black/20 backdrop-blur-xs px-1.5 py-0.5 rounded tracking-wider uppercase shrink-0 border border-white/10">{{ config('app.name') }}</span>
                </div>
            </div>

            <!-- Notification Bell -->
            <button class="bg-white/10 backdrop-blur-md border border-white/20 size-8 rounded-full text-white hover:bg-white/20 transition flex items-center justify-center shrink-0" title="Notifikasi">
                <flux:icon name="bell" class="size-4" variant="outline" />
            </button>
        </div>

        <!-- Profil Siswa Menonjol (Data Singkat Siswa) -->
        <div class="relative z-10 flex items-center justify-between gap-3.5">
            <div class="min-w-0 flex-1">
                <div class="text-[11px] text-emerald-200/90 font-medium tracking-wide">Selamat Datang,</div>
                <h1 class="font-extrabold text-lg sm:text-xl text-white tracking-tight truncate leading-tight drop-shadow-sm mt-0.5">
                    {{ $siswa?->nama ?? 'Siswa' }}
                </h1>
                
                <!-- Data Singkat: Kelas & NIS -->
                <div class="flex items-center gap-1.5 mt-2 flex-wrap">
                    @if(optional($siswa?->kelas)->nama)
                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold bg-white/20 backdrop-blur-md text-white px-2 py-0.5 rounded-lg border border-white/15 shadow-2xs">
                            <flux:icon name="academic-cap" class="size-3.5 text-emerald-200" />
                            {{ $siswa->kelas->nama }}
                        </span>
                    @endif
                    <span class="inline-flex items-center text-[11px] font-medium text-emerald-100 bg-black/15 backdrop-blur-md px-2 py-0.5 rounded-lg border border-white/10">
                        NIS: {{ $siswa?->nis ?? '-' }}
                    </span>
                    @if($siswa?->nisn)
                        <span class="inline-flex items-center text-[11px] font-medium text-emerald-100 bg-black/15 backdrop-blur-md px-2 py-0.5 rounded-lg border border-white/10">
                            NISN: {{ $siswa?->nisn }}
                        </span>
                    @endif
                </div>
            </div>

            <!-- Foto Profil Siswa Menonjol -->
            <a href="{{ route('siswa.profil') }}" wire:navigate class="relative shrink-0 group block" title="Buka Profil {{ $siswa?->nama }}">
                <div class="size-15 sm:size-16 rounded-2xl border-2 border-white/50 p-0.5 bg-white/15 backdrop-blur-md overflow-hidden shadow-lg group-hover:scale-105 group-hover:border-white transition-all duration-300" style="width: 60px; height: 60px; min-width: 60px; min-height: 60px;">
                    @if($siswa?->foto)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($siswa->foto) }}" class="size-full object-cover rounded-xl" style="width: 100%; height: 100%; max-width: 60px; max-height: 60px; object-fit: cover;" alt="Foto Siswa">
                    @else
                        <img src="{{ asset($siswa?->jenis_kelamin === 'p' ? 'images/girl.jpg' : 'images/boy.jpg') }}" class="size-full object-cover rounded-xl" style="width: 100%; height: 100%; max-width: 60px; max-height: 60px; object-fit: cover;" alt="Foto Default">
                    @endif
                </div>
                <span class="absolute -bottom-0.5 -right-0.5 size-3 rounded-full bg-emerald-400 ring-2 ring-emerald-800"></span>
            </a>
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
                <a href="{{ route('siswa.tabungan.riwayat') }}" wire:navigate class="w-full bg-emerald-600 hover:bg-emerald-700 transition text-white text-sm font-semibold px-4 py-3.5 rounded-2xl flex items-center justify-center gap-2 shadow-md shadow-emerald-600/20">
                    <flux:icon name="clock" class="size-5" variant="outline" />
                    Riwayat Setoran Tabungan
                </a>
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
            <a href="{{ route('siswa.tabungan.list') }}" wire:navigate class="flex flex-col items-center gap-2.5 group">
                <div class="w-14 h-14 bg-white border border-gray-100 rounded-[20px] flex items-center justify-center text-emerald-600 shadow-sm group-hover:shadow-md group-hover:border-emerald-200 group-hover:bg-emerald-50 transition-all duration-300">
                    <flux:icon name="wallet" class="size-6" variant="outline" />
                </div>
                <span class="text-[11px] text-gray-600 font-semibold group-hover:text-emerald-700 transition-colors">Tabungan</span>
            </a>
            <a href="{{ route('siswa.tagihan') }}" wire:navigate class="flex flex-col items-center gap-2.5 group">
                <div class="w-14 h-14 bg-white border border-gray-100 rounded-[20px] flex items-center justify-center text-emerald-600 shadow-sm group-hover:shadow-md group-hover:border-emerald-200 group-hover:bg-emerald-50 transition-all duration-300">
                    <flux:icon name="document-text" class="size-6" variant="outline" />
                </div>
                <span class="text-[11px] text-gray-600 font-semibold group-hover:text-emerald-700 transition-colors">Tagihan</span>
            </a>
            <a href="{{ route('siswa.transaksi') }}" wire:navigate class="flex flex-col items-center gap-2.5 group">
                <div class="w-14 h-14 bg-white border border-gray-100 rounded-[20px] flex items-center justify-center text-emerald-600 shadow-sm group-hover:shadow-md group-hover:border-emerald-200 group-hover:bg-emerald-50 transition-all duration-300">
                    <flux:icon name="arrows-right-left" class="size-6" variant="outline" />
                </div>
                <span class="text-[11px] text-gray-600 font-semibold group-hover:text-emerald-700 transition-colors">Pembayaran</span>
            </a>
            {{-- <a href="#" class="flex flex-col items-center gap-2.5 group">
                <div class="w-14 h-14 bg-white border border-gray-100 rounded-[20px] flex items-center justify-center text-emerald-600 shadow-sm group-hover:shadow-md group-hover:border-emerald-200 group-hover:bg-emerald-50 transition-all duration-300">
                    <flux:icon name="users" class="size-6" variant="outline" />
                </div>
                <span class="text-[11px] text-gray-600 font-semibold group-hover:text-emerald-700 transition-colors">Absensi</span>
            </a> --}}
            <a href="{{ route('siswa.kartu') }}" wire:navigate class="flex flex-col items-center gap-2.5 group">
                <div class="w-14 h-14 bg-white border border-gray-100 rounded-[20px] flex items-center justify-center text-emerald-600 shadow-sm group-hover:shadow-md group-hover:border-emerald-200 group-hover:bg-emerald-50 transition-all duration-300">
                    <flux:icon name="credit-card" class="size-6" variant="outline" />
                </div>
                <span class="text-[11px] text-gray-600 font-semibold group-hover:text-emerald-700 transition-colors">Kartu</span>
            </a>
            {{-- <a href="{{ route('siswa.profil') }}" class="flex flex-col items-center gap-2.5 group">
                <div class="w-14 h-14 bg-white border border-gray-100 rounded-[20px] flex items-center justify-center text-emerald-600 shadow-sm group-hover:shadow-md group-hover:border-emerald-200 group-hover:bg-emerald-50 transition-all duration-300">
                    <flux:icon name="user" class="size-6" variant="outline" />
                </div>
                <span class="text-[11px] text-gray-600 font-semibold group-hover:text-emerald-700 transition-colors">Akun</span>
            </a> --}}
            {{-- <a href="#" class="flex flex-col items-center gap-2.5 group">
                <div class="w-14 h-14 bg-white border border-gray-100 rounded-[20px] flex items-center justify-center text-emerald-600 shadow-sm group-hover:shadow-md group-hover:border-emerald-200 group-hover:bg-emerald-50 transition-all duration-300">
                    <flux:icon name="squares-2x2" class="size-6" variant="outline" />
                </div>
                <span class="text-[11px] text-gray-600 font-semibold group-hover:text-emerald-700 transition-colors">Lainnya</span>
            </a> --}}
        </div>
    </div>

    <!-- Tagihan Belum Lunas -->
    @if(isset($tagihanTerakhir) && $tagihanTerakhir->isNotEmpty())
        <div class="px-6 mt-9">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-gray-800 text-sm uppercase tracking-wider flex items-center gap-2">
                    <span class="w-1.5 h-4 bg-emerald-500 rounded-full"></span>
                    Tagihan Terbaru
                </h3>
                <a href="{{ route('siswa.tagihan') }}" wire:navigate class="text-emerald-600 text-xs font-semibold hover:text-emerald-700 transition flex items-center gap-0.5">
                    Lihat Semua <flux:icon name="chevron-right" class="size-3.5" />
                </a>
            </div>

            <div class="space-y-3">
                @foreach ($tagihanTerakhir as $tagihan)
                    <a href="{{ route('siswa.tagihan') }}" wire:navigate class="block bg-white rounded-2xl shadow-xs border border-gray-100 p-4 transition-all duration-200 hover:shadow-md hover:border-rose-100 active:scale-[0.99] group">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="size-11 rounded-2xl flex items-center justify-center shrink-0 bg-rose-50 text-rose-600 group-hover:bg-rose-100 transition-colors">
                                    <flux:icon name="document-text" class="size-5" />
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold text-sm text-gray-800 truncate group-hover:text-rose-700 transition-colors">
                                        {{ optional($tagihan->kas)->nama ?? 'Tagihan' }}
                                    </div>
                                    <div class="text-[11px] text-gray-400 mt-0.5 flex items-center gap-1.5">
                                        <span>{{ $tagihan->created_at->format('d M Y') }}</span>
                                        <span>&bull;</span>
                                        <span class="font-mono text-[10px] text-gray-400">{{ $tagihan->kode }}</span>
                                    </div>
                                    @if($tagihan->keterangan)
                                        <div class="text-[11px] text-gray-500 mt-0.5 truncate leading-tight">
                                            {{ $tagihan->keterangan }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="text-right shrink-0">
                                <div class="font-extrabold text-sm text-gray-800">
                                    Rp {{ number_format($tagihan->jumlah, 0, ',', '.') }}
                                </div>
                                <span class="inline-block mt-1 px-2 py-0.5 rounded-full text-[9px] font-bold tracking-wider bg-rose-50 text-rose-700 border border-rose-200/80">
                                    BELUM LUNAS
                                </span>
                            </div>
                        </div>

                        @if($tagihan->bayar > 0)
                            <div class="mt-2.5 pt-2 border-t border-gray-50 flex justify-between items-center text-xs">
                                <span class="text-gray-400 text-[11px]">Telah dibayar: <strong class="text-gray-700 font-medium">Rp {{ number_format($tagihan->bayar, 0, ',', '.') }}</strong></span>
                                <span class="font-bold text-rose-600 text-xs">Sisa Rp {{ number_format($tagihan->jumlah - $tagihan->bayar, 0, ',', '.') }}</span>
                            </div>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    @endif

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
        
        @if(isset($informasiSticky) && $informasiSticky)
            <!-- Modern News Banner (Sticky / Garis Merah) -->
            <a href="{{ route('siswa.informasi', ['id' => $informasiSticky->id]) }}" class="block bg-gray-900 rounded-[24px] p-6 text-white relative overflow-hidden mb-5 shadow-lg shadow-gray-900/10 group">
                <div class="absolute right-0 top-0 w-40 h-40 bg-emerald-500 rounded-full blur-[60px] opacity-20 -mr-10 -mt-10 pointer-events-none"></div>
                <div class="absolute left-0 bottom-0 w-32 h-32 bg-emerald-600 rounded-full blur-[50px] opacity-20 -ml-10 -mb-10 pointer-events-none"></div>
                <div class="relative z-10">
                    <div class="inline-flex items-center gap-1.5 bg-emerald-500/20 text-emerald-400 text-[10px] font-bold px-2.5 py-1 rounded-md mb-3 border border-emerald-500/20 tracking-wide uppercase">
                        <flux:icon name="bookmark" class="size-2.5 text-emerald-400" />
                        {{ $informasiSticky->kategori }}
                    </div>
                    <div class="font-bold text-[17px] mb-3 leading-snug text-white/95 group-hover:text-emerald-300 transition-colors">
                        {{ $informasiSticky->judul }}
                    </div>
                    <p class="text-xs text-gray-300 line-clamp-2 mb-4 leading-relaxed">
                        {{ strip_tags($informasiSticky->konten) }}
                    </p>
                    <div class="bg-emerald-500 group-hover:bg-emerald-600 text-white font-semibold px-6 py-2.5 rounded-xl text-xs transition shadow-md shadow-emerald-500/20 w-full flex items-center justify-center gap-2">
                        Baca Selengkapnya
                        <flux:icon name="arrow-right" class="size-3.5 group-hover:translate-x-1 transition-transform" />
                    </div>
                </div>
            </a>
        @else
            <!-- Modern News Banner (Default Placeholder) -->
            <div class="bg-gray-900 rounded-[24px] p-6 text-white relative overflow-hidden mb-5 shadow-lg shadow-gray-900/10">
                <div class="absolute right-0 top-0 w-40 h-40 bg-emerald-500 rounded-full blur-[60px] opacity-20 -mr-10 -mt-10 pointer-events-none"></div>
                <div class="absolute left-0 bottom-0 w-32 h-32 bg-emerald-600 rounded-full blur-[50px] opacity-20 -ml-10 -mb-10 pointer-events-none"></div>
                <div class="relative z-10">
                    <div class="inline-block bg-emerald-500/20 text-emerald-400 text-[10px] font-bold px-2.5 py-1 rounded-md mb-3 border border-emerald-500/20 tracking-wide">INFORMASI</div>
                    <div class="font-bold text-[17px] mb-2 leading-snug text-white/95">Pusat Informasi & Pengumuman</div>
                    <p class="text-xs text-gray-300 mb-5 leading-relaxed">Dapatkan kabar terbaru seputar kegiatan akademik dan pengumuman sekolah langsung di sini.</p>
                    <a href="{{ route('siswa.informasi') }}" class="bg-emerald-500 hover:bg-emerald-600 text-white font-semibold px-6 py-2.5 rounded-xl text-xs transition shadow-md shadow-emerald-500/20 w-full flex items-center justify-center gap-2">
                        Buka Halaman Informasi
                        <flux:icon name="arrow-right" class="size-3.5" />
                    </a>
                </div>
            </div>
        @endif

        <!-- Berita Lainnya (Garis Biru) -->
        @if(isset($informasiLainnya) && $informasiLainnya->isNotEmpty())
            <div class="space-y-4">
                @foreach($informasiLainnya as $item)
                    <a href="{{ route('siswa.informasi', ['id' => $item->id]) }}" class="block bg-white rounded-[20px] p-5 shadow-sm border border-gray-100 hover:border-emerald-200 hover:shadow-md transition-all duration-300 group">
                        <div class="flex items-center justify-between mb-2.5">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                <span class="text-[11px] text-gray-500 font-semibold tracking-wide">
                                    {{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}
                                </span>
                            </div>
                            <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full capitalize">
                                {{ $item->kategori }}
                            </span>
                        </div>
                        <div class="font-bold text-[15px] text-gray-800 mb-1.5 group-hover:text-emerald-700 transition-colors line-clamp-1">
                            {{ $item->judul }}
                        </div>
                        <div class="text-[13px] text-gray-500 leading-relaxed line-clamp-2">
                            {{ strip_tags($item->konten) }}
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>
