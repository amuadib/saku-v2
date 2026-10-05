<div>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <flux:button variant="ghost" size="sm" icon="arrow-left" href="{{ route('admin.tagihan.index') }}" wire:navigate />
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $tagihan && $tagihan->exists ? 'Edit Tagihan' : 'Buat Tagihan Baru' }}
            </h2>
        </div>
    </x-slot>

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg max-w-3xl mx-auto mb-6">
        <div class="p-6">
            <form wire:submit="save" class="space-y-6">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label>Kode Tagihan</flux:label>
                        <flux:input wire:model="kode" placeholder="Otomatis atau manual" />
                        <flux:error name="kode" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Tanggal Kadaluarsa (Opsional)</flux:label>
                        <flux:input type="date" wire:model="tanggal_kadaluarsa" />
                        <flux:error name="tanggal_kadaluarsa" />
                    </flux:field>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label>Pilih Siswa</flux:label>
                        <flux:select wire:model="siswa_id" searchable placeholder="Pilih Siswa...">
                            <option value="">-- Pilih Siswa --</option>
                            @foreach($siswa_list as $s)
                                <option value="{{ $s->id }}">{{ $s->nis }} - {{ $s->nama }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="siswa_id" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Jenis Tagihan (Kas)</flux:label>
                        <flux:select wire:model="kas_id">
                            <option value="">-- Pilih Kas Tagihan --</option>
                            @foreach($kas_list as $k)
                                <option value="{{ $k->id }}">{{ $k->nama }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="kas_id" />
                    </flux:field>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label>Jumlah Tagihan (Rp)</flux:label>
                        <flux:input type="number" wire:model="jumlah" placeholder="0" />
                        <flux:error name="jumlah" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Jumlah Dibayar (Rp)</flux:label>
                        <flux:input type="number" wire:model="bayar" placeholder="0" />
                        <flux:error name="bayar" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Keterangan Tambahan</flux:label>
                    <flux:textarea wire:model="keterangan" rows="3" placeholder="Opsional" />
                    <flux:error name="keterangan" />
                </flux:field>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <flux:button variant="ghost" href="{{ route('admin.tagihan.index') }}" wire:navigate>Batal</flux:button>
                    <flux:button type="submit" variant="primary">Simpan Tagihan</flux:button>
                </div>

            </form>
        </div>
    </div>
</div>
