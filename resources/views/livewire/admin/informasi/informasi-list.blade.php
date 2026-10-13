<div>
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Pengelolaan Informasi Siswa
                </h2>
                <p class="text-sm text-gray-500 dark:text-zinc-400">
                    Kelola berita, pengumuman, dan informasi yang ditampilkan pada portal siswa
                </p>
            </div>
            <flux:button variant="primary" icon="plus" href="{{ route('admin.informasi.create') }}" wire:navigate>
                Tambah Informasi
            </flux:button>
        </div>
    </x-slot>

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mb-6">
        <div class="p-6">
            <!-- Filter Bar -->
            <div class="mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari judul atau isi informasi..." icon="magnifying-glass" clearable />

                <flux:select wire:model.live="filter_kategori" placeholder="Semua Kategori">
                    <flux:select.option value="">Semua Kategori</flux:select.option>
                    @foreach($kategoris as $kat)
                        <flux:select.option value="{{ $kat }}">{{ $kat }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model.live="filter_lembaga" placeholder="Semua Sasaran">
                    <flux:select.option value="">Semua Sasaran</flux:select.option>
                    <flux:select.option value="all">Semua Lembaga (Umum)</flux:select.option>
                    @foreach($lembaga_list as $key => $val)
                        <flux:select.option value="{{ $key }}">{{ $val['nama'] ?? $val }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model.live="filter_status" placeholder="Semua Status">
                    <flux:select.option value="">Semua Status</flux:select.option>
                    <flux:select.option value="1">Aktif (Tampil di Siswa)</flux:select.option>
                    <flux:select.option value="0">Nonaktif (Disembunyikan)</flux:select.option>
                </flux:select>
            </div>

            @if (session()->has('message'))
                <flux:callout variant="success" class="mb-4">
                    {{ session('message') }}
                </flux:callout>
            @endif

            <!-- Table List -->
            <flux:table>
                <flux:table.columns>
                    <flux:table.column class="w-20">Gambar</flux:table.column>
                    <flux:table.column>Informasi</flux:table.column>
                    <flux:table.column>Kategori</flux:table.column>
                    <flux:table.column>Sasaran</flux:table.column>
                    <flux:table.column>Tanggal</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column class="w-24">Sticky</flux:table.column>
                    <flux:table.column class="text-right">Aksi</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($informasi_items as $info)
                        <flux:table.row wire:key="info-{{ $info->id }}">
                            <!-- Thumbnail -->
                            <flux:table.cell>
                                <div class="size-14 rounded-xl overflow-hidden bg-gray-100 dark:bg-zinc-700 border border-gray-200 dark:border-zinc-700 flex items-center justify-center shrink-0 shadow-sm relative">
                                    @if($info->gambar_url)
                                        <img src="{{ $info->gambar_url }}" class="size-full object-cover" alt="{{ $info->judul }}" />
                                    @else
                                        <div class="size-full bg-emerald-50 dark:bg-emerald-950/40 flex items-center justify-center text-emerald-600 dark:text-emerald-400 font-bold text-xs">
                                            INFO
                                        </div>
                                    @endif

                                    @if($info->is_sticky)
                                        <span class="absolute top-1 right-1 size-2 rounded-full bg-red-500 ring-2 ring-white"></span>
                                    @endif
                                </div>
                            </flux:table.cell>

                            <!-- Judul & Snippet -->
                            <flux:table.cell>
                                <div class="font-bold text-gray-900 dark:text-white text-sm leading-snug line-clamp-1 mb-1 flex items-center gap-1.5">
                                    @if($info->is_sticky)
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-red-700 bg-red-50 dark:bg-red-950/40 dark:text-red-300 px-1.5 py-0.5 rounded border border-red-200/60 shrink-0">
                                            <flux:icon name="bookmark" class="size-2.5 text-red-600" />
                                            STICKY
                                        </span>
                                    @endif
                                    <span class="truncate">{{ $info->judul }}</span>
                                </div>
                                <div class="text-xs text-gray-500 dark:text-zinc-400 line-clamp-2 leading-relaxed">
                                    {{ Str::limit(strip_tags($info->konten), 120) }}
                                </div>
                            </flux:table.cell>

                            <!-- Kategori -->
                            <flux:table.cell>
                                <flux:badge color="blue" size="sm" class="capitalize">
                                    {{ $info->kategori }}
                                </flux:badge>
                            </flux:table.cell>

                            <!-- Sasaran Lembaga -->
                            <flux:table.cell>
                                @if(empty($info->lembaga_id) || $info->lembaga_id == 99)
                                    <flux:badge color="purple" size="sm">
                                        Semua Lembaga
                                    </flux:badge>
                                @else
                                    <flux:badge color="zinc" size="sm">
                                        {{ config('custom.lembaga.'.$info->lembaga_id.'.singkatan', 'Lembaga #'.$info->lembaga_id) }}
                                    </flux:badge>
                                @endif
                            </flux:table.cell>

                            <!-- Tanggal -->
                            <flux:table.cell class="whitespace-nowrap text-xs text-gray-600 dark:text-zinc-300 font-medium">
                                {{ \Carbon\Carbon::parse($info->tanggal)->translatedFormat('d M Y') }}
                            </flux:table.cell>

                            <!-- Status Toggle -->
                            <flux:table.cell>
                                <button type="button" wire:click="toggleStatus('{{ $info->id }}')" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold transition {{ $info->is_aktif ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-300' : 'bg-gray-100 text-gray-500 hover:bg-gray-200 dark:bg-zinc-800 dark:text-zinc-400' }}" title="Klik untuk mengubah status aktif">
                                    <span class="size-1.5 rounded-full {{ $info->is_aktif ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                                    <span>{{ $info->is_aktif ? 'Aktif' : 'Nonaktif' }}</span>
                                </button>
                            </flux:table.cell>

                            <!-- Sticky Toggle -->
                            <flux:table.cell>
                                <button type="button" wire:click="toggleSticky('{{ $info->id }}')" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold transition {{ $info->is_sticky ? 'bg-red-50 text-red-700 hover:bg-red-100 dark:bg-red-950/40 dark:text-red-300 border border-red-200/60' : 'bg-gray-100 text-gray-400 hover:bg-gray-200 dark:bg-zinc-800 dark:text-zinc-500' }}" title="Klik untuk mengubah status sticky">
                                    <flux:icon name="bookmark" class="size-3 {{ $info->is_sticky ? 'text-red-600' : 'text-gray-400' }}" />
                                    <span>{{ $info->is_sticky ? 'Ya' : 'Tidak' }}</span>
                                </button>
                            </flux:table.cell>

                            <!-- Actions -->
                            <flux:table.cell class="text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <flux:button size="sm" href="{{ route('admin.informasi.edit', $info->id) }}" wire:navigate icon="pencil" color="yellow" title="Edit"></flux:button>
                                    <flux:button size="sm" variant="danger" wire:click="delete('{{ $info->id }}')" wire:confirm="Hapus informasi ini?" icon="trash" title="Hapus"></flux:button>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="8" class="text-center py-8 text-gray-400 dark:text-zinc-500">
                                <flux:icon name="newspaper" class="size-10 mx-auto mb-2 opacity-50" />
                                <p class="text-sm font-medium">Belum ada informasi yang ditambahkan.</p>
                                <p class="text-xs text-gray-400 mt-1">Klik tombol "Tambah Informasi" untuk membuat pengumuman baru.</p>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>

            <div class="mt-6">
                {{ $informasi_items->links() }}
            </div>
        </div>
    </div>
</div>
