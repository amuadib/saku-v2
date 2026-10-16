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

            <!-- Tombol About Program -->
            <flux:modal.trigger name="about-program">
                <button type="button" class="bg-white/20 hover:bg-white/30 backdrop-blur-md border border-white/30 size-8.5 rounded-full text-white shadow-sm active:scale-95 transition flex items-center justify-center shrink-0 cursor-pointer" title="Tentang Program">
                    <flux:icon name="information-circle" class="size-5" variant="outline" />
                </button>
            </flux:modal.trigger>
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

    <!-- Modal Tentang Program (About - Bright & Clean Mode) -->
    <flux:modal name="about-program" variant="bare" class="w-full max-w-sm sm:max-w-md mx-auto p-4">
        <div class="bg-white rounded-[28px] p-6 shadow-2xl border border-gray-100 space-y-5 relative text-gray-800 max-h-[88vh] overflow-y-auto">
            <!-- Close Button -->
            <div class="absolute top-4 right-4 z-10">
                <flux:modal.close>
                    <button type="button" class="size-8 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-400 hover:text-gray-700 flex items-center justify-center transition active:scale-95 cursor-pointer" aria-label="Tutup">
                        <flux:icon name="x-mark" class="size-4" />
                    </button>
                </flux:modal.close>
            </div>

            <!-- Header: Logo, Nama, Versi (Bright & Fresh) -->
            <div class="text-center pt-2">
                <div class="size-16 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 shadow-md shadow-emerald-500/20 p-2 mx-auto flex items-center justify-center mb-3 ring-4 ring-emerald-50">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="Logo" class="size-full object-contain" />
                    @else
                        <x-app-logo-icon class="size-9 text-white" />
                    @endif
                </div>

                <div class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 text-xs font-bold px-3 py-1 rounded-full border border-emerald-100 mb-2">
                    <flux:icon name="sparkles" class="size-3.5 text-emerald-600" />
                    <span>{{ config('app.name', 'SAKU') }}</span>
                    <span class="text-[10px] bg-emerald-600 text-white px-1.5 py-0.5 rounded-full font-semibold">v2.0</span>
                </div>

                <h3 class="text-lg font-bold text-gray-900 tracking-tight">
                    {{ config('custom.app.nama', 'Sistem Administrasi Keuangan') }}
                </h3>
                
                <p class="text-xs text-gray-500 mt-1.5 leading-relaxed max-w-xs mx-auto">
                    {{ config('custom.app.keterangan') ?? 'Platform portal transparansi administrasi keuangan, tabungan, dan layanan informasi sekolah.' }}
                </p>
            </div>

            <div class="h-px bg-gray-100 my-1"></div>

            <!-- Fitur & Layanan Utama (Bright cards with colorful badges) -->
            <div class="space-y-2.5">
                <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Fitur & Layanan Utama</div>
                
                <div class="space-y-2 text-left">
                    <div class="flex items-start gap-3 p-3 rounded-2xl bg-slate-50 border border-slate-100 hover:bg-slate-100/60 transition">
                        <div class="size-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 mt-0.5">
                            <flux:icon name="wallet" class="size-4" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-bold text-gray-900">Buku Tabungan Digital</div>
                            <div class="text-[11px] text-gray-500 leading-snug mt-0.5">Pantau saldo, mutasi setor & penarikan tabungan siswa secara transparan.</div>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 p-3 rounded-2xl bg-slate-50 border border-slate-100 hover:bg-slate-100/60 transition">
                        <div class="size-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 mt-0.5">
                            <flux:icon name="banknotes" class="size-4" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-bold text-gray-900">Monitoring Tagihan Sekolah</div>
                            <div class="text-[11px] text-gray-500 leading-snug mt-0.5">Cek rincian kewajiban administrasi & SPP yang belum lunas secara akurat.</div>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 p-3 rounded-2xl bg-slate-50 border border-slate-100 hover:bg-slate-100/60 transition">
                        <div class="size-8 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center shrink-0 mt-0.5">
                            <flux:icon name="chat-bubble-oval-left-ellipsis" class="size-4" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-bold text-gray-900">Layanan Bantuan & Chat</div>
                            <div class="text-[11px] text-gray-500 leading-snug mt-0.5">Konsultasi dan konfirmasi pembayaran langsung ke petugas tata usaha.</div>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 p-3 rounded-2xl bg-slate-50 border border-slate-100 hover:bg-slate-100/60 transition">
                        <div class="size-8 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center shrink-0 mt-0.5">
                            <flux:icon name="megaphone" class="size-4" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-bold text-gray-900">Informasi & Pengumuman</div>
                            <div class="text-[11px] text-gray-500 leading-snug mt-0.5">Akses berita kegiatan dan edaran resmi dari madrasah / sekolah.</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kartu Pengembang / Developer Info (Menarik, Rapi & Interaktif) -->
            <div class="p-4 bg-gradient-to-br from-emerald-50/90 via-teal-50/40 to-slate-50 rounded-2xl border border-emerald-150/80 shadow-xs space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-1.5">
                        <span class="size-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="text-[10px] font-bold tracking-wider text-emerald-800 uppercase">Informasi Pengembang</span>
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-0.5">
                    <div class="size-11 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white flex items-center justify-center font-extrabold text-sm shadow-md shadow-emerald-600/20 shrink-0 ring-2 ring-white">
                        AM
                    </div>
                    <div class="min-w-0 flex-1 text-left">
                        <div class="font-bold text-sm text-gray-900 truncate flex items-center gap-1.5">
                            <span>AMuadib</span>
                            <flux:icon name="check-badge" class="size-4 text-emerald-600 shrink-0" variant="solid" />
                        </div>
                        <div class="text-[11px] text-gray-500 flex items-center gap-1">
                            <flux:icon name="code-bracket" class="size-3 text-emerald-600 shrink-0" />
                            <span class="truncate">Fullstack Developer</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-2 pt-1">
                    <!-- Email -->
                    <a href="mailto:a.muadib@gmail.com" class="flex items-center gap-2.5 px-3 py-2 rounded-xl bg-white hover:bg-emerald-50/60 border border-gray-150 hover:border-emerald-200 text-gray-700 hover:text-emerald-700 transition group shadow-2xs">
                        <div class="size-7 rounded-lg bg-emerald-50 text-emerald-600 group-hover:bg-emerald-100 flex items-center justify-center shrink-0 transition">
                            <flux:icon name="envelope" class="size-3.5" />
                        </div>
                        <div class="min-w-0 flex-1 text-left">
                            <div class="text-[9px] font-semibold text-gray-400 uppercase tracking-wider">Email</div>
                            <div class="text-[11px] font-medium text-gray-800 group-hover:text-emerald-700 truncate">a.muadib@gmail.com</div>
                        </div>
                    </a>

                    <!-- GitHub -->
                    <a href="https://github.com/amuadib" target="_blank" rel="noopener noreferrer" class="flex items-center gap-2.5 px-3 py-2 rounded-xl bg-white hover:bg-zinc-50 border border-gray-150 hover:border-zinc-300 text-gray-700 hover:text-zinc-900 transition group shadow-2xs">
                        <div class="size-7 rounded-lg bg-zinc-100 text-zinc-700 group-hover:bg-zinc-200 flex items-center justify-center shrink-0 transition">
                            <svg class="size-3.5 shrink-0" viewBox="0 0 24 24" fill="currentColor">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/>
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1 text-left">
                            <div class="text-[9px] font-semibold text-gray-400 uppercase tracking-wider">GitHub</div>
                            <div class="text-[11px] font-medium text-gray-800 group-hover:text-zinc-900 truncate">github.com/amuadib</div>
                        </div>
                        <flux:icon name="arrow-top-right-on-square" class="size-3 text-gray-300 group-hover:text-gray-500 mr-1 shrink-0" />
                    </a>
                </div>

                <div class="text-center text-[10px] text-gray-400 pt-1 border-t border-emerald-100/60">
                    &copy; 2026 YPIB • Hak Cipta Dilindungi
                </div>
            </div>

            <!-- Tombol Tutup (Clean, bright, solid emerald) -->
            <div class="pt-1">
                <flux:modal.close>
                    <button type="button" class="w-full py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs tracking-wide shadow-sm active:scale-[0.99] transition cursor-pointer">
                        Tutup Informasi
                    </button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>
</div>
