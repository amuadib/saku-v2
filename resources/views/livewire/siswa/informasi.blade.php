<div class="bg-[#f8f9fc] min-h-screen pb-28">
    <!-- Header -->
    <div class="px-5 pt-10 pb-4 bg-white/90 backdrop-blur-md shadow-sm border-b border-gray-100 sticky top-0 z-30">
        <div class="flex items-center gap-3">
            <a href="{{ route('siswa.dashboard') }}" wire:navigate class="p-2 -ml-2 rounded-full text-gray-500 hover:bg-gray-100 active:scale-95 transition">
                <flux:icon name="arrow-left" class="size-5" />
            </a>
            <div>
                <h2 class="font-bold text-lg text-gray-800 leading-tight">Info dan Berita</h2>
                <p class="text-[11px] text-gray-400">Pengumuman & informasi akademik terbaru</p>
            </div>
        </div>
        
        <!-- Category Pills -->
        <div class="flex gap-2 mt-4 overflow-x-auto pb-1 scrollbar-hide" style="scrollbar-width: none;">
            <button type="button" wire:click="setKategori('Semua')" class="whitespace-nowrap px-4 py-1.5 rounded-full text-xs font-semibold transition active:scale-95 {{ $kategori === 'Semua' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                Semua
            </button>
            @foreach($categories as $cat)
                <button type="button" wire:click="setKategori('{{ $cat }}')" class="whitespace-nowrap px-4 py-1.5 rounded-full text-xs font-semibold transition capitalize active:scale-95 {{ $kategori === $cat ? 'bg-emerald-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    {{ $cat }}
                </button>
            @endforeach
        </div>
    </div>
    
    <div class="px-5 pt-5 space-y-4">
        @forelse($informasi_items as $item)
            <div wire:key="info-card-{{ $item->id }}" wire:click="selectInformasi('{{ $item->id }}')" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden cursor-pointer hover:shadow-md hover:border-emerald-200 transition-all duration-200 active:scale-[0.99] group">
                <div class="flex h-32">
                    <!-- Thumbnail -->
                    <div class="w-1/3 min-w-[110px] h-full bg-emerald-50/50 relative overflow-hidden flex items-center justify-center shrink-0">
                        @if($item->gambar_url)
                            <img src="{{ $item->gambar_url }}" alt="{{ $item->judul }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-emerald-500/10 via-teal-500/10 to-emerald-600/20 flex flex-col items-center justify-center text-emerald-600 p-2 text-center">
                                <flux:icon name="newspaper" class="size-7 mb-1 opacity-70" />
                                <span class="text-[10px] font-bold uppercase tracking-wider">{{ $item->kategori }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- Content snippet -->
                    <div class="w-2/3 p-3.5 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between gap-1 mb-1.5">
                                <span class="text-[10px] font-semibold text-gray-400">
                                    {{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}
                                </span>
                                <div class="flex items-center gap-1">
                                    @if($item->is_sticky)
                                        <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-full text-[9px] font-bold bg-red-50 text-red-700 border border-red-100">
                                            <flux:icon name="bookmark" class="size-2.5 text-red-500" />
                                            Sticky
                                        </span>
                                    @endif
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 capitalize">
                                        {{ $item->kategori }}
                                    </span>
                                </div>
                            </div>
                            <h3 class="font-bold text-sm text-gray-900 mb-1 leading-snug line-clamp-2 group-hover:text-emerald-700 transition-colors">
                                {{ $item->judul }}
                            </h3>
                            <p class="text-[11px] text-gray-500 leading-relaxed line-clamp-2">
                                {{ strip_tags($item->konten) }}
                            </p>
                        </div>

                        <div class="text-[10px] text-emerald-600 font-semibold flex items-center gap-1 pt-1">
                            Baca selengkapnya
                            <flux:icon name="arrow-right" class="size-3 group-hover:translate-x-0.5 transition-transform" />
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-3xl p-8 text-center border border-gray-100 shadow-sm mt-4">
                <div class="size-16 rounded-full bg-emerald-50 text-emerald-600 mx-auto flex items-center justify-center mb-3">
                    <flux:icon name="newspaper" class="size-8" />
                </div>
                <h3 class="font-bold text-sm text-gray-800 mb-1">Belum Ada Informasi</h3>
                <p class="text-xs text-gray-400 max-w-xs mx-auto leading-relaxed">
                    Saat ini belum ada pengumuman atau berita pada kategori {{ $kategori !== 'Semua' ? '"'.$kategori.'"' : 'ini' }}. Silakan cek kembali nanti.
                </p>
            </div>
        @endforelse
    </div>

    <!-- Modal Detail Informasi -->
    @if($selectedInformasi)
        <div class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-end sm:items-center justify-center p-0 sm:p-4 overflow-y-auto" wire:click.self="closeDetail">
            <div class="bg-white w-full max-w-md rounded-t-[32px] sm:rounded-3xl max-h-[90vh] overflow-y-auto shadow-2xl relative animate-in fade-in slide-in-from-bottom-6 duration-200">
                <!-- Close Button Top Right -->
                <button type="button" wire:click="closeDetail" class="absolute top-4 right-4 z-20 size-8 bg-black/40 hover:bg-black/60 backdrop-blur-md text-white rounded-full flex items-center justify-center transition">
                    <flux:icon name="x-mark" class="size-4" />
                </button>

                <!-- Full Banner Image if available -->
                @if($selectedInformasi->gambar_url)
                    <div class="w-full h-52 bg-gray-100 relative overflow-hidden">
                        <img src="{{ $selectedInformasi->gambar_url }}" class="w-full h-full object-cover" alt="{{ $selectedInformasi->judul }}" />
                    </div>
                @else
                    <div class="w-full h-24 bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700 flex items-center justify-center text-white relative">
                        <flux:icon name="newspaper" class="size-10 opacity-30" />
                    </div>
                @endif

                <!-- Article Body -->
                <div class="p-6 space-y-4">
                    <!-- Badges Row -->
                    <div class="flex items-center justify-between gap-2 flex-wrap">
                        <div class="flex items-center gap-2">
                            @if($selectedInformasi->is_sticky)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-red-50 text-red-700 border border-red-200">
                                    <flux:icon name="bookmark" class="size-3 text-red-500" />
                                    Sticky
                                </span>
                            @endif
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 capitalize">
                                {{ $selectedInformasi->kategori }}
                            </span>
                            @if(empty($selectedInformasi->lembaga_id) || $selectedInformasi->lembaga_id == 99)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-gray-100 text-gray-600">
                                    Umum (Semua Lembaga)
                                </span>
                            @endif
                        </div>
                        <span class="text-xs text-gray-400 font-medium">
                            {{ \Carbon\Carbon::parse($selectedInformasi->tanggal)->translatedFormat('d F Y') }}
                        </span>
                    </div>

                    <!-- Title -->
                    <h2 class="font-bold text-lg text-gray-900 leading-snug">
                        {{ $selectedInformasi->judul }}
                    </h2>

                    <!-- Divider -->
                    <div class="w-full h-px bg-gray-100"></div>

                    <!-- Content Text -->
                    <div class="text-sm text-gray-700 leading-relaxed space-y-3 whitespace-pre-line font-sans">
                        {!! nl2br(e($selectedInformasi->konten)) !!}
                    </div>

                    <!-- Bottom Close Button -->
                    <div class="pt-4 border-t border-gray-100">
                        <button type="button" wire:click="closeDetail" class="w-full py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-xs rounded-2xl transition">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
