<div>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <flux:button variant="ghost" size="sm" icon="arrow-left" href="{{ route('admin.kelas.index') }}" wire:navigate />
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $kelas && $kelas->exists ? 'Edit Kelas' : 'Tambah Kelas' }}
            </h2>
        </div>
    </x-slot>

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg max-w-2xl mx-auto mb-6">
        <div class="p-6">
            <form wire:submit="save" class="space-y-6">
                
                <flux:field>
                    <flux:label>Nama Kelas</flux:label>
                    <flux:input wire:model="nama" placeholder="Contoh: Kelas XA" />
                    <flux:error name="nama" />
                </flux:field>

                <flux:field>
                    <flux:label>Tingkat (Opsional)</flux:label>
                    <flux:input type="number" wire:model="tingkat" placeholder="Contoh: 10" />
                    <flux:error name="tingkat" />
                </flux:field>

                <flux:field>
                    <flux:label>Periode</flux:label>
                    <flux:select wire:model="periode_id" placeholder="Pilih Periode">
                        <option value="">-- Pilih Periode --</option>
                        @foreach($periodes as $periode)
                            <option value="{{ $periode->id }}">{{ $periode->nama }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="periode_id" />
                </flux:field>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <flux:button variant="ghost" href="{{ route('admin.kelas.index') }}" wire:navigate>Batal</flux:button>
                    <flux:button type="submit" variant="primary">Simpan</flux:button>
                </div>

            </form>
        </div>
    </div>
</div>
