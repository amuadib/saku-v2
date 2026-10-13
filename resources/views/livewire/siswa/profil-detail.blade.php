<div class="bg-[#f8f9fc] min-h-screen pb-28">
    <!-- Sticky Top Header -->
    <div class="px-5 pt-10 pb-4 bg-white/90 backdrop-blur-md shadow-sm border-b border-gray-100 sticky top-0 z-30">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('siswa.profil') }}" wire:navigate class="p-2 -ml-2 rounded-full text-gray-500 hover:bg-gray-100 active:scale-95 transition">
                    <flux:icon name="arrow-left" class="size-5" />
                </a>
                <div>
                    <h2 class="font-bold text-lg text-gray-800 leading-tight">Profil Siswa</h2>
                    <p class="text-[11px] text-gray-400">Informasi data diri dan akademik</p>
                </div>
            </div>

            @if(!$isEditing)
                <button type="button" wire:click="toggleEdit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200/60 shadow-sm active:scale-95 transition">
                    <flux:icon name="pencil-square" class="size-3.5" />
                    <span>Edit Data</span>
                </button>
            @else
                <button type="button" wire:click="cancelEdit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 hover:bg-gray-200 active:scale-95 transition">
                    <flux:icon name="x-mark" class="size-3.5" />
                    <span>Batal</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Notification Alert -->
    @if(session()->has('message'))
        <div class="px-5 pt-4">
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl flex items-center gap-3 shadow-sm text-sm">
                <div class="p-1 bg-emerald-500 text-white rounded-full flex-shrink-0">
                    <flux:icon name="check" class="size-3.5" />
                </div>
                <div class="flex-1 font-medium">{{ session('message') }}</div>
            </div>
        </div>
    @endif

    @if(session()->has('error'))
        <div class="px-5 pt-4">
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-2xl flex items-center gap-3 shadow-sm text-sm">
                <div class="p-1 bg-red-500 text-white rounded-full flex-shrink-0">
                    <flux:icon name="exclamation-triangle" class="size-3.5" />
                </div>
                <div class="flex-1 font-medium">{{ session('error') }}</div>
            </div>
        </div>
    @endif

    @if(!$isEditing)
        <!-- ================= VIEW MODE ================= -->
        <div class="px-5 pt-5 space-y-5">
            <!-- Hero Card -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 relative overflow-hidden text-center">
                <!-- Background decorative shapes -->
                <div class="absolute -right-10 -top-10 w-36 h-36 bg-emerald-50/80 rounded-full blur-2xl pointer-events-none"></div>
                <div class="absolute -left-10 -bottom-10 w-36 h-36 bg-teal-50/80 rounded-full blur-2xl pointer-events-none"></div>

                <div class="relative z-10 flex flex-col items-center">
                    <!-- Photo with ring -->
                    <div class="relative mb-3">
                        <div class="size-24 rounded-full overflow-hidden border-4 border-white shadow-md bg-gray-100 flex items-center justify-center">
                            @if($siswa && $siswa->foto)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($siswa->foto) }}" class="w-full h-full object-cover" alt="Foto Siswa">
                            @else
                                <img src="{{ asset(($siswa && $siswa->jenis_kelamin === 'p') ? 'images/girl.jpg' : 'images/boy.jpg') }}" class="w-full h-full object-cover" alt="Foto Default">
                            @endif
                        </div>
                        <div class="absolute bottom-1 right-1 size-4 bg-emerald-500 border-2 border-white rounded-full" title="Aktif"></div>
                    </div>

                    <!-- Name and Basic Info -->
                    <h3 class="font-bold text-lg text-gray-900 leading-tight mb-1">{{ $siswa->nama ?? 'Siswa' }}</h3>
                    <p class="text-xs text-gray-500 mb-3">{{ $lembaga['nama'] ?? 'Lembaga Pendidikan' }}</p>

                    <!-- Badges Row -->
                    <div class="flex items-center justify-center flex-wrap gap-1.5 mb-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Kelas: {{ optional($siswa->kelas)->nama ?? '-' }}
                        </span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                            {{ $siswa->jenis_kelamin === 'p' ? 'Perempuan' : 'Laki-laki' }}
                        </span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100/70 text-emerald-800">
                            <span class="size-1.5 rounded-full bg-emerald-600 animate-pulse"></span>
                            {{ config('custom.siswa.status.'.($siswa->status ?? 1), 'Aktif') }}
                        </span>
                    </div>

                    <!-- NISN & NIS Pill -->
                    <div class="w-full bg-gray-50 rounded-2xl py-2.5 px-4 flex items-center justify-around border border-gray-100 text-xs text-gray-600">
                        <div class="text-center">
                            <div class="text-[10px] uppercase tracking-wider text-gray-400 font-medium">NIS</div>
                            <div class="font-bold text-gray-800 mt-0.5">{{ $siswa->nis ?: '-' }}</div>
                        </div>
                        <div class="h-6 w-px bg-gray-200"></div>
                        <div class="text-center">
                            <div class="text-[10px] uppercase tracking-wider text-gray-400 font-medium">NISN</div>
                            <div class="font-bold text-gray-800 mt-0.5">{{ $siswa->nisn ?: '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 1: Data Diri / Identitas -->
            <div class="bg-white rounded-3xl p-5 shadow-sm border border-gray-100 space-y-4">
                <div class="flex items-center gap-2 pb-3 border-b border-gray-100">
                    <div class="p-1.5 bg-emerald-50 text-emerald-600 rounded-lg">
                        <flux:icon name="user" class="size-4" variant="solid" />
                    </div>
                    <h4 class="font-bold text-sm text-gray-800">Data Pribadi</h4>
                </div>

                <div class="space-y-3.5 text-sm">
                    <div class="flex justify-between items-start gap-4">
                        <span class="text-gray-400 text-xs">Nama Lengkap</span>
                        <span class="font-semibold text-gray-800 text-right">{{ $siswa->nama ?? '-' }}</span>
                    </div>

                    <div class="flex justify-between items-start gap-4">
                        <span class="text-gray-400 text-xs">NIK (No. KTP/KIA)</span>
                        <span class="font-semibold text-gray-800 text-right font-mono">{{ $siswa->nik ?: '-' }}</span>
                    </div>

                    <div class="flex justify-between items-start gap-4">
                        <span class="text-gray-400 text-xs">Tempat, Tanggal Lahir</span>
                        <span class="font-semibold text-gray-800 text-right">
                            {{ $siswa->tempat_lahir ?: '-' }}, {{ $siswa->tanggal_lahir ? \Carbon\Carbon::parse($siswa->tanggal_lahir)->translatedFormat('d F Y') : '-' }}
                        </span>
                    </div>

                    <div class="flex justify-between items-start gap-4">
                        <span class="text-gray-400 text-xs">Jenis Kelamin</span>
                        <span class="font-semibold text-gray-800 text-right">
                            {{ $siswa->jenis_kelamin === 'p' ? 'Perempuan' : 'Laki-laki' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Section 2: Kontak & Alamat -->
            <div class="bg-white rounded-3xl p-5 shadow-sm border border-gray-100 space-y-4">
                <div class="flex items-center gap-2 pb-3 border-b border-gray-100">
                    <div class="p-1.5 bg-emerald-50 text-emerald-600 rounded-lg">
                        <flux:icon name="phone" class="size-4" variant="solid" />
                    </div>
                    <h4 class="font-bold text-sm text-gray-800">Kontak & Alamat</h4>
                </div>

                <div class="space-y-3.5 text-sm">
                    <div class="flex justify-between items-start gap-4">
                        <span class="text-gray-400 text-xs">No. Telepon/WA</span>
                        @if($siswa->telepon)
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $siswa->telepon) }}" target="_blank" class="font-semibold text-emerald-600 hover:text-emerald-700 flex items-center gap-1 text-right">
                                {{ $siswa->telepon }}
                                <flux:icon name="arrow-top-right-on-square" class="size-3.5" />
                            </a>
                        @else
                            <span class="font-semibold text-gray-400">-</span>
                        @endif
                    </div>

                    <div class="flex justify-between items-start gap-4">
                        <span class="text-gray-400 text-xs">Email</span>
                        @if($siswa->email)
                            <a href="mailto:{{ $siswa->email }}" class="font-semibold text-emerald-600 hover:text-emerald-700 text-right truncate max-w-[200px]">
                                {{ $siswa->email }}
                            </a>
                        @else
                            <span class="font-semibold text-gray-400">-</span>
                        @endif
                    </div>

                    <div class="flex justify-between items-start gap-4">
                        <span class="text-gray-400 text-xs">Alamat Tinggal</span>
                        <span class="font-semibold text-gray-800 text-right leading-relaxed max-w-[220px]">
                            {{ $siswa->alamat ?: '-' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Section 3: Data Orang Tua -->
            <div class="bg-white rounded-3xl p-5 shadow-sm border border-gray-100 space-y-4">
                <div class="flex items-center gap-2 pb-3 border-b border-gray-100">
                    <div class="p-1.5 bg-emerald-50 text-emerald-600 rounded-lg">
                        <flux:icon name="users" class="size-4" variant="solid" />
                    </div>
                    <h4 class="font-bold text-sm text-gray-800">Orang Tua / Wali</h4>
                </div>

                <div class="space-y-3.5 text-sm">
                    <div class="flex justify-between items-start gap-4">
                        <span class="text-gray-400 text-xs">Nama Ayah</span>
                        <span class="font-semibold text-gray-800 text-right">{{ $siswa->nama_ayah ?: '-' }}</span>
                    </div>

                    <div class="flex justify-between items-start gap-4">
                        <span class="text-gray-400 text-xs">Nama Ibu</span>
                        <span class="font-semibold text-gray-800 text-right">{{ $siswa->nama_ibu ?: '-' }}</span>
                    </div>
                </div>
            </div>

            <!-- Section 4: Data Akademik (Official) -->
            <div class="bg-white rounded-3xl p-5 shadow-sm border border-gray-100 space-y-4">
                <div class="flex items-center gap-2 pb-3 border-b border-gray-100">
                    <div class="p-1.5 bg-emerald-50 text-emerald-600 rounded-lg">
                        <flux:icon name="academic-cap" class="size-4" variant="solid" />
                    </div>
                    <h4 class="font-bold text-sm text-gray-800">Informasi Akademik</h4>
                </div>

                <div class="space-y-3.5 text-sm">
                    <div class="flex justify-between items-start gap-4">
                        <span class="text-gray-400 text-xs">Lembaga / Sekolah</span>
                        <span class="font-semibold text-gray-800 text-right">{{ $lembaga['nama'] ?? '-' }}</span>
                    </div>

                    <div class="flex justify-between items-start gap-4">
                        <span class="text-gray-400 text-xs">Kelas Terdaftar</span>
                        <span class="font-semibold text-gray-800 text-right">{{ optional($siswa->kelas)->nama ?? '-' }}</span>
                    </div>

                    <div class="flex justify-between items-start gap-4">
                        <span class="text-gray-400 text-xs">Status Kesiswaan</span>
                        <span class="font-semibold text-emerald-600 text-right">
                            {{ config('custom.siswa.status.'.($siswa->status ?? 1), 'Aktif') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Bottom CTA Button to Edit -->
            <div class="pt-2">
                <button type="button" wire:click="toggleEdit" class="w-full bg-emerald-600 hover:bg-emerald-700 active:scale-[0.99] text-white font-bold py-3.5 px-6 rounded-2xl flex items-center justify-center gap-2 shadow-lg shadow-emerald-600/25 transition">
                    <flux:icon name="pencil-square" class="size-5" />
                    <span>Ubah Data Profil</span>
                </button>
            </div>
        </div>

    @else
        <!-- ================= EDIT MODE ================= -->
        <form wire:submit="save" class="px-5 pt-4 space-y-5">
            <!-- Edit Mode Header Alert -->
            <div class="bg-amber-50 border border-amber-200/80 rounded-2xl p-4 flex items-start gap-3">
                <div class="p-1 bg-amber-500 text-white rounded-lg flex-shrink-0 mt-0.5">
                    <flux:icon name="pencil" class="size-4" />
                </div>
                <div class="text-xs text-amber-900 leading-relaxed">
                    <strong class="font-semibold">Mode Edit Aktif:</strong> Anda dapat memperbarui foto profil, kontak, alamat, dan data diri. Pastikan data yang dimasukkan benar.
                </div>
            </div>

            <!-- Foto Profil Upload Card -->
            <div class="bg-white rounded-3xl p-5 shadow-sm border border-gray-100 text-center">
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Foto Profil Siswa</label>
                
                <div class="flex flex-col items-center justify-center">
                    <div class="relative group cursor-pointer">
                        <label for="foto-input" class="cursor-pointer block relative">
                            <div class="size-28 rounded-full overflow-hidden border-4 border-emerald-100 shadow-md bg-gray-50 flex items-center justify-center relative">
                                @if($foto)
                                    <img src="{{ $foto->temporaryUrl() }}" class="w-full h-full object-cover" alt="Preview Foto">
                                @elseif($existingFoto)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($existingFoto) }}" class="w-full h-full object-cover" alt="Foto Siswa">
                                @else
                                    <img src="{{ asset($jenis_kelamin === 'p' ? 'images/girl.jpg' : 'images/boy.jpg') }}" class="w-full h-full object-cover" alt="Foto Default">
                                @endif

                                <!-- Overlay hover effect -->
                                <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity rounded-full">
                                    <flux:icon name="camera" class="size-6 text-white" />
                                </div>
                            </div>

                            <!-- Camera Badge Icon -->
                            <div class="absolute bottom-1 right-1 size-8 bg-emerald-600 hover:bg-emerald-700 text-white rounded-full flex items-center justify-center shadow-md border-2 border-white transition">
                                <flux:icon name="camera" class="size-4" />
                            </div>
                        </label>

                        <input type="file" id="foto-input" wire:model="foto" accept="image/png,image/jpeg,image/jpg" class="hidden" />
                    </div>

                    <div wire:loading wire:target="foto" class="text-xs text-emerald-600 font-semibold mt-2 flex items-center gap-1.5">
                        <svg class="animate-spin size-3.5 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                        </svg>
                        <span>Mengunggah foto...</span>
                    </div>

                    <p class="text-[11px] text-gray-400 mt-2">Format JPG/PNG, ukuran maks. 2MB</p>

                    @error('foto')
                        <p class="text-xs text-red-500 font-medium mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Form: Data Diri -->
            <div class="bg-white rounded-3xl p-5 shadow-sm border border-gray-100 space-y-4">
                <div class="flex items-center gap-2 pb-2 border-b border-gray-100">
                    <div class="p-1.5 bg-emerald-50 text-emerald-600 rounded-lg">
                        <flux:icon name="user" class="size-4" variant="solid" />
                    </div>
                    <h4 class="font-bold text-sm text-gray-800">Identitas Diri</h4>
                </div>

                <!-- Nama Lengkap -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="nama" placeholder="Masukkan nama lengkap" class="w-full bg-gray-50 border border-gray-200 rounded-2xl px-4 py-3 text-sm text-gray-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition @error('nama') border-red-300 ring-2 ring-red-100 @enderror">
                    @error('nama')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- NIK -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">NIK (Nomor Induk Kependudukan)</label>
                    <input type="text" wire:model="nik" maxlength="16" placeholder="16 digit NIK" class="w-full bg-gray-50 border border-gray-200 rounded-2xl px-4 py-3 text-sm text-gray-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition @error('nik') border-red-300 ring-2 ring-red-100 @enderror">
                    @error('nik')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Jenis Kelamin -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Jenis Kelamin <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center justify-center gap-2 p-3 rounded-2xl border cursor-pointer transition {{ $jenis_kelamin === 'l' ? 'bg-emerald-50 border-emerald-500 text-emerald-700 font-semibold' : 'bg-gray-50 border-gray-200 text-gray-600 hover:bg-gray-100' }}">
                            <input type="radio" wire:model.live="jenis_kelamin" value="l" class="hidden">
                            <flux:icon name="user" class="size-4" />
                            <span class="text-xs">Laki-laki</span>
                        </label>
                        <label class="flex items-center justify-center gap-2 p-3 rounded-2xl border cursor-pointer transition {{ $jenis_kelamin === 'p' ? 'bg-emerald-50 border-emerald-500 text-emerald-700 font-semibold' : 'bg-gray-50 border-gray-200 text-gray-600 hover:bg-gray-100' }}">
                            <input type="radio" wire:model.live="jenis_kelamin" value="p" class="hidden">
                            <flux:icon name="user" class="size-4" />
                            <span class="text-xs">Perempuan</span>
                        </label>
                    </div>
                    @error('jenis_kelamin')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tempat & Tanggal Lahir -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Tempat Lahir</label>
                        <input type="text" wire:model="tempat_lahir" placeholder="Kota lahir" class="w-full bg-gray-50 border border-gray-200 rounded-2xl px-4 py-3 text-sm text-gray-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition @error('tempat_lahir') border-red-300 @enderror">
                        @error('tempat_lahir')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Tanggal Lahir</label>
                        <input type="date" wire:model="tanggal_lahir" class="w-full bg-gray-50 border border-gray-200 rounded-2xl px-4 py-3 text-sm text-gray-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition @error('tanggal_lahir') border-red-300 @enderror">
                        @error('tanggal_lahir')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Form: Kontak & Alamat -->
            <div class="bg-white rounded-3xl p-5 shadow-sm border border-gray-100 space-y-4">
                <div class="flex items-center gap-2 pb-2 border-b border-gray-100">
                    <div class="p-1.5 bg-emerald-50 text-emerald-600 rounded-lg">
                        <flux:icon name="phone" class="size-4" variant="solid" />
                    </div>
                    <h4 class="font-bold text-sm text-gray-800">Kontak & Alamat</h4>
                </div>

                <!-- Telepon / WhatsApp -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">No. Telepon / WhatsApp</label>
                    <input type="tel" wire:model="telepon" placeholder="Contoh: 081234567890" class="w-full bg-gray-50 border border-gray-200 rounded-2xl px-4 py-3 text-sm text-gray-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition @error('telepon') border-red-300 @enderror">
                    @error('telepon')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Email</label>
                    <input type="email" wire:model="email" placeholder="Contoh: siswa@email.com" class="w-full bg-gray-50 border border-gray-200 rounded-2xl px-4 py-3 text-sm text-gray-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition @error('email') border-red-300 @enderror">
                    @error('email')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Alamat -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Alamat Lengkap</label>
                    <textarea wire:model="alamat" rows="3" placeholder="Nama jalan, RT/RW, Dusun, Desa, Kecamatan, Kabupaten" class="w-full bg-gray-50 border border-gray-200 rounded-2xl px-4 py-3 text-sm text-gray-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition resize-none @error('alamat') border-red-300 @enderror"></textarea>
                    @error('alamat')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Form: Data Orang Tua -->
            <div class="bg-white rounded-3xl p-5 shadow-sm border border-gray-100 space-y-4">
                <div class="flex items-center gap-2 pb-2 border-b border-gray-100">
                    <div class="p-1.5 bg-emerald-50 text-emerald-600 rounded-lg">
                        <flux:icon name="users" class="size-4" variant="solid" />
                    </div>
                    <h4 class="font-bold text-sm text-gray-800">Orang Tua / Wali</h4>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Nama Ayah</label>
                    <input type="text" wire:model="nama_ayah" placeholder="Masukkan nama ayah kandung / wali" class="w-full bg-gray-50 border border-gray-200 rounded-2xl px-4 py-3 text-sm text-gray-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition @error('nama_ayah') border-red-300 @enderror">
                    @error('nama_ayah')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Nama Ibu</label>
                    <input type="text" wire:model="nama_ibu" placeholder="Masukkan nama ibu kandung" class="w-full bg-gray-50 border border-gray-200 rounded-2xl px-4 py-3 text-sm text-gray-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition @error('nama_ibu') border-red-300 @enderror">
                    @error('nama_ibu')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Readonly Notice: Data Akademik -->
            <div class="bg-gray-100/80 rounded-2xl p-4 border border-gray-200/60 text-xs text-gray-500 space-y-2">
                <div class="flex items-center gap-2 font-semibold text-gray-700">
                    <flux:icon name="lock-closed" class="size-4 text-gray-400" />
                    <span>Data Akademik Terkunci</span>
                </div>
                <p class="leading-relaxed">
                    Nomor identitas sekolah (NIS: <span class="font-mono text-gray-700">{{ $nis ?: '-' }}</span>, NISN: <span class="font-mono text-gray-700">{{ $nisn ?: '-' }}</span>) dan Kelas dikelola resmi oleh administrator/tata usaha sekolah.
                </p>
            </div>

            <!-- Sticky Bottom Action Buttons -->
            <div class="pt-2 flex items-center gap-3">
                <button type="button" wire:click="cancelEdit" class="w-1/3 py-3.5 px-4 bg-gray-100 hover:bg-gray-200 active:scale-95 text-gray-700 font-semibold text-sm rounded-2xl transition text-center">
                    Batal
                </button>
                <button type="submit" wire:loading.attr="disabled" class="w-2/3 bg-emerald-600 hover:bg-emerald-700 active:scale-[0.99] text-white font-bold py-3.5 px-6 rounded-2xl flex items-center justify-center gap-2 shadow-lg shadow-emerald-600/30 transition disabled:opacity-75">
                    <span wire:loading.remove wire:target="save" class="flex items-center gap-2">
                        <flux:icon name="check" class="size-4" />
                        Simpan Perubahan
                    </span>
                    <span wire:loading wire:target="save" class="flex items-center gap-2">
                        <svg class="animate-spin size-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                        </svg>
                        Menyimpan...
                    </span>
                </button>
            </div>
        </form>
    @endif
</div>
