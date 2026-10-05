<div>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <flux:button variant="ghost" size="sm" icon="arrow-left" href="{{ route('admin.pengaduan.index') }}" wire:navigate />
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $pengaduan && $pengaduan->exists ? 'Detail Pengaduan' : 'Buat Pengaduan Baru' }}
            </h2>
        </div>
    </x-slot>

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg max-w-2xl mx-auto mb-6">
        <div class="p-6">
            <form wire:submit="save" class="space-y-6">
                
                <flux:field>
                    <flux:label>Siswa / Pelapor</flux:label>
                    <flux:select wire:model="siswa_id" searchable placeholder="Pilih Siswa...">
                        <option value="">-- Pilih Siswa --</option>
                        @foreach($siswa_list as $s)
                            <option value="{{ $s->id }}">{{ $s->nis }} - {{ $s->nama }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="siswa_id" />
                </flux:field>

                <flux:field>
                    <flux:label>Isi Laporan / Pengaduan</flux:label>
                    <flux:textarea wire:model="laporan" rows="4" placeholder="Jelaskan detail pengaduan..." />
                    <flux:error name="laporan" />
                </flux:field>

                <flux:field>
                    <flux:label>Status Pengaduan</flux:label>
                    <flux:select wire:model="status">
                        <option value="0">Baru</option>
                        <option value="1">Sedang Diproses</option>
                        <option value="2">Selesai / Ditutup</option>
                    </flux:select>
                    <flux:error name="status" />
                </flux:field>

                <flux:field>
                    <flux:label>Tanggapan / Keterangan (Opsional)</flux:label>
                    <flux:textarea wire:model="keterangan" rows="3" placeholder="Tanggapan atau tindak lanjut dari petugas..." />
                    <flux:error name="keterangan" />
                </flux:field>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <flux:button variant="ghost" href="{{ route('admin.pengaduan.index') }}" wire:navigate>Batal</flux:button>
                    <flux:button type="submit" variant="primary">Simpan Data</flux:button>
                </div>

            </form>
        </div>
    </div>
</div>
