<div>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <flux:button variant="ghost" size="sm" icon="arrow-left" href="{{ route('admin.siswa.index') }}" wire:navigate />
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $siswa && $siswa->exists ? 'Edit Siswa' : 'Tambah Siswa' }}
            </h2>
        </div>
    </x-slot>

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg max-w-4xl mx-auto mb-6">
        <div class="p-6">
            <form wire:submit="save" class="space-y-6">
                
                <!-- Identitas -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">Identitas Siswa</h3>
                    
                    <flux:field class="mb-6">
                        <flux:label>Foto Siswa</flux:label>
                        <div class="flex items-end gap-4 mt-2">
                            @if ($foto)
                                <img src="{{ $foto->temporaryUrl() }}" class="size-24 object-cover rounded-md border border-gray-200">
                            @elseif ($siswa && $siswa->foto)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($siswa->foto) }}" class="size-24 object-cover rounded-md border border-gray-200">
                            @else
                                <img src="{{ asset($jenis_kelamin === 'p' ? 'girl.jpg' : 'boy.jpg') }}" class="size-24 object-cover rounded-md border border-gray-200 dark:border-gray-700" alt="Foto Default">
                            @endif
                            <div class="flex-1 max-w-sm">
                                <flux:input type="file" wire:model="foto" accept="image/*" />
                                @if ($foto || ($siswa && $siswa->foto))
                                    <div class="mt-2">
                                        <flux:button size="sm" variant="danger" wire:click="deleteFoto" wire:confirm="Yakin ingin menghapus foto ini?">Hapus Foto</flux:button>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <flux:error name="foto" />
                    </flux:field>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4">
                        <flux:field>
                            <flux:label>NIK</flux:label>
                            <flux:input wire:model="nik" placeholder="Nomor Induk Kependudukan" />
                            <flux:error name="nik" />
                        </flux:field>

                        <flux:field>
                            <flux:label>No. Akte Lahir</flux:label>
                            <flux:input wire:model="no_akte_lahir" placeholder="Nomor Akte Lahir" />
                            <flux:error name="no_akte_lahir" />
                        </flux:field>

                        <flux:field>
                            <flux:label>No. KK</flux:label>
                            <flux:input wire:model="no_kk" placeholder="Nomor Kartu Keluarga" />
                            <flux:error name="no_kk" />
                        </flux:field>
                    </div>

                    <flux:field class="mb-4">
                        <flux:label>Nama Lengkap</flux:label>
                        <flux:input wire:model="nama" placeholder="Nama lengkap siswa" />
                        <flux:error name="nama" />
                    </flux:field>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4">
                        <flux:field>
                            <flux:label>Tempat Lahir</flux:label>
                            <flux:input wire:model="tempat_lahir" />
                            <flux:error name="tempat_lahir" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Tanggal Lahir</flux:label>
                            <flux:input type="date" wire:model="tanggal_lahir" />
                            <flux:error name="tanggal_lahir" />
                        </flux:field>

                        <flux:radio.group wire:model="jenis_kelamin" label="Jenis Kelamin" class="flex flex-row gap-4 items-start mt-1">
                            <flux:radio value="l" label="Laki-laki" />
                            <flux:radio value="p" label="Perempuan" />
                        </flux:radio.group>
                    </div>

                    <flux:field class="mb-4">
                        <flux:label>Alamat Lengkap</flux:label>
                        <flux:textarea wire:model="alamat" rows="2" />
                        <flux:error name="alamat" />
                    </flux:field>
                </div>

                <!-- Kontak & Orang Tua -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">Kontak & Orang Tua</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <flux:field>
                            <flux:label>Nama Ayah</flux:label>
                            <flux:input wire:model="nama_ayah" />
                            <flux:error name="nama_ayah" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Nama Ibu</flux:label>
                            <flux:input wire:model="nama_ibu" />
                            <flux:error name="nama_ibu" />
                        </flux:field>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <flux:field>
                            <flux:label>No. Telepon/WhatsApp</flux:label>
                            <flux:input wire:model="telepon" />
                            <flux:error name="telepon" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Email (Opsional)</flux:label>
                            <flux:input type="email" wire:model="email" />
                            <flux:error name="email" />
                        </flux:field>
                    </div>
                </div>

                <!-- Akademik -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">Akademik</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <flux:field>
                            <flux:label>NIS</flux:label>
                            <flux:input wire:model="nis" placeholder="Nomor Induk Siswa" />
                            <flux:error name="nis" />
                        </flux:field>

                        <flux:field>
                            <flux:label>NISN</flux:label>
                            <flux:input wire:model="nisn" placeholder="Nomor Induk Siswa Nasional" />
                            <flux:error name="nisn" />
                        </flux:field>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <flux:field>
                            <flux:label>Kelas</flux:label>
                            <flux:select wire:model="kelas_id">
                                <option value="">-- Pilih Kelas --</option>
                                @foreach($kelas as $k)
                                    <option value="{{ $k->id }}">{{ $k->nama }}</option>
                                @endforeach
                            </flux:select>
                            <flux:error name="kelas_id" />
                        </flux:field>

                        <flux:radio.group wire:model="status" label="Status Siswa" class="flex flex-row flex-wrap gap-4 items-start">
                            @foreach(config('custom.siswa.status', []) as $key => $val)
                                <flux:radio value="{{ (string)$key }}" label="{{ $val }}" />
                            @endforeach
                        </flux:radio.group>
                    </div>

                    <div class="mb-4">
                        <flux:checkbox.group wire:model="label" label="Label Siswa" class="flex flex-row flex-wrap gap-4 items-start">
                            @foreach(\App\Models\Tag::all() as $tag)
                                <flux:checkbox value="{{ $tag->id }}" label="{{ $tag->name }}" />
                            @endforeach
                        </flux:checkbox.group>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <flux:button variant="ghost" href="{{ route('admin.siswa.index') }}" wire:navigate>Batal</flux:button>
                    <flux:button type="submit" variant="primary">Simpan Data Siswa</flux:button>
                </div>

            </form>
        </div>
    </div>
</div>
