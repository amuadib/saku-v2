<div>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Data Pengaduan
            </h2>
            <flux:button variant="primary" href="{{ route('admin.pengaduan.create') }}" wire:navigate>Tambah Pengaduan</flux:button>
        </div>
    </x-slot>

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mb-6">
        <div class="p-6">
            <div class="mb-4 flex gap-4">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari pelapor atau isi laporan..." icon="magnifying-glass" class="w-full sm:w-1/3" />
            </div>

            @if (session()->has('message'))
                <flux:callout variant="success" class="mb-4">
                    {{ session('message') }}
                </flux:callout>
            @endif

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Tanggal / Pelapor</flux:table.column>
                    <flux:table.column>Laporan</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column>Aksi</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($pengaduans as $pengaduan)
                        <flux:table.row>
                            <flux:table.cell>
                                <div class="text-xs text-gray-500">{{ $pengaduan->created_at->format('d M Y H:i') }}</div>
                                <div class="font-medium">{{ optional($pengaduan->siswa)->nama ?? '-' }}</div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="truncate max-w-xs" title="{{ $pengaduan->laporan }}">
                                    {{ $pengaduan->laporan }}
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($pengaduan->status == '0')
                                    <flux:badge color="red">Baru</flux:badge>
                                @elseif($pengaduan->status == '1')
                                    <flux:badge color="yellow">Diproses</flux:badge>
                                @elseif($pengaduan->status == '2')
                                    <flux:badge color="green">Selesai</flux:badge>
                                @else
                                    <flux:badge color="gray">{{ $pengaduan->status }}</flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:button size="sm" href="{{ route('admin.pengaduan.edit', $pengaduan->id) }}" wire:navigate icon="pencil" color="yellow"></flux:button>
                                <flux:button size="sm" variant="danger" wire:click="delete('{{ $pengaduan->id }}')" wire:confirm="Apakah Anda yakin ingin menghapus pengaduan ini?" icon="trash"></flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="4" class="text-center">Data tidak ditemukan.</flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>

            <div class="mt-4">
                {{ $pengaduans->links() }}
            </div>
        </div>
    </div>
</div>
