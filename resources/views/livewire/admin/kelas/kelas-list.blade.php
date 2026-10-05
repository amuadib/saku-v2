<div>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Daftar Kelas
            </h2>
            <flux:button variant="primary" href="{{ route('admin.kelas.create') }}" wire:navigate>Tambah Kelas</flux:button>
        </div>
    </x-slot>

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mb-6">
        <div class="p-6">
            <div class="mb-4 flex flex-col sm:flex-row gap-4">
                <flux:select wire:model.live="filter_periode" class="w-full sm:w-1/4">
                    <flux:select.option value="">Semua Periode</flux:select.option>
                    @foreach($periodes as $p)
                        <flux:select.option value="{{ $p->id }}">{{ $p->nama }} {!! $p->aktif ? '(Aktif)' : '' !!}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:select wire:model.live="filter_lembaga" class="w-full sm:w-1/4">
                    <flux:select.option value="">Semua Lembaga</flux:select.option>
                    @foreach(config('custom.lembaga') as $key => $val)
                        <flux:select.option value="{{ $key }}">{{ $val }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            @if (session()->has('message'))
                <flux:callout variant="success" class="mb-4">
                    {{ session('message') }}
                </flux:callout>
            @endif

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Nama Kelas</flux:table.column>
                    <flux:table.column>Lembaga</flux:table.column>
                    <flux:table.column>Tingkat</flux:table.column>
                    <flux:table.column>Periode</flux:table.column>
                    <flux:table.column>Aksi</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($kelas_items as $kelas)
                        <flux:table.row>
                            <flux:table.cell>{{ $kelas->nama }}</flux:table.cell>
                            <flux:table.cell>{{ config('custom.lembaga.'.$kelas->lembaga_id) }}</flux:table.cell>
                            <flux:table.cell>{{ $kelas->tingkat ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $kelas->periode->nama ?? '-' }}</flux:table.cell>
                            <flux:table.cell>
                                <div class="flex gap-2">
                                    <flux:button size="sm" href="{{ route('admin.kelas.edit', $kelas->id) }}" wire:navigate icon="pencil" color="yellow"></flux:button>
                                    <flux:button size="sm" variant="danger" wire:click="delete('{{ $kelas->id }}')" wire:confirm="Apakah Anda yakin ingin menghapus kelas ini?" icon="trash"></flux:button>
                                </div>
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
                {{ $kelas_items->links() }}
            </div>
        </div>
    </div>
</div>
