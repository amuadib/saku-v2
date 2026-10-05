<div>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <flux:button variant="ghost" size="sm" icon="arrow-left" href="{{ route('admin.tabungan.index') }}" wire:navigate />
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $tabungan && $tabungan->exists ? 'Edit Tabungan' : 'Tambah Tabungan' }}
            </h2>
        </div>
    </x-slot>

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg max-w-2xl mx-auto mb-6">
        <div class="p-6">
            <form wire:submit="save" class="space-y-6">
                
                <flux:field>
                    <flux:label>Siswa</flux:label>
                    <flux:select wire:model="siswa_id" searchable placeholder="Pilih Siswa...">
                        <option value="">-- Pilih Siswa --</option>
                        @foreach($siswa_list as $s)
                            <option value="{{ $s->id }}">{{ $s->nis }} - {{ $s->nama }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="siswa_id" />
                </flux:field>

                <flux:field>
                    <flux:label>Kas Tabungan</flux:label>
                    <flux:select wire:model="kas_id">
                        <option value="">-- Pilih Kas Tabungan --</option>
                        @foreach($kas_list as $k)
                            <option value="{{ $k->id }}">{{ $k->nama }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="kas_id" />
                </flux:field>

                <flux:field>
                    <flux:label>Saldo Awal / Saat Ini (Rp)</flux:label>
                    <flux:input type="number" wire:model="saldo" placeholder="0" />
                    <flux:error name="saldo" />
                </flux:field>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <flux:button variant="ghost" href="{{ route('admin.tabungan.index') }}" wire:navigate>Batal</flux:button>
                    <flux:button type="submit" variant="primary">Simpan</flux:button>
                </div>

            </form>
        </div>
    </div>
</div>
