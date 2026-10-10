<div class="bg-gray-50 min-h-screen pb-24">
    <!-- Header -->
    <div class="px-6 pt-10 pb-4 bg-white shadow-sm border-b border-gray-100 sticky top-0 z-20">
        <div class="flex items-center gap-3">
            <a href="{{ route('siswa.dashboard') }}" wire:navigate class="p-2 -ml-2 rounded-full text-gray-500 hover:bg-gray-100 transition">
                <flux:icon name="arrow-left" class="size-5" />
            </a>
            <h2 class="font-bold text-xl text-gray-800">Kartu Pelajar Digital</h2>
        </div>
    </div>

    <!-- Card Container -->
    <div class="px-6 pt-8">
        <!-- Digital ID Card -->
        <div class="relative bg-white rounded-xl shadow-2xl overflow-hidden max-w-sm mx-auto flex flex-col border border-gray-200 font-sans" style="min-height: 520px;">
            
            <!-- Thin Top Emerald Bar -->
            <div class="h-2 w-full bg-emerald-600"></div>

            <!-- Header: Logo and School Name -->
            <div class="bg-white px-5 py-4 flex items-center gap-4 z-10">
                <div class="flex-shrink-0 size-14 rounded-full border-[2.5px] border-amber-500 flex items-center justify-center bg-white p-1.5">
                     <x-app-logo-icon class="size-full object-contain text-emerald-600" />
                </div>
                <div class="font-black text-emerald-600 text-xl uppercase tracking-tighter leading-tight flex-1">
                    {{ config('custom.lembaga.' . $siswa->lembaga_id, 'SCHOOL NAME') }}
                </div>
            </div>

            <!-- Student ID Band -->
            <div class="bg-emerald-600 py-2.5 flex flex-col items-center justify-center z-10 relative shadow-sm">
                <div class="text-amber-400 font-bold uppercase tracking-widest text-sm mb-0.5 text-center leading-none">KARTU IDENTITAS PELAJAR</div>
                <div class="text-white font-bold tracking-widest text-[15px] leading-none">{{ $siswa->nis ?? $siswa->nisn ?? '-' }}</div>
            </div>

            <!-- Middle Body -->
            <div class="relative flex-1 bg-white flex flex-col items-center pt-8 pb-6">
                <!-- Faint Circle Left -->
                <div class="absolute left-[-50px] top-12 w-32 h-32 rounded-full border-[3px] border-amber-200 opacity-50"></div>
                <!-- Faint Dots Right -->
                <div class="absolute right-6 top-[40%] flex flex-col gap-1.5 opacity-60">
                     <div class="size-3 rounded-md bg-white border-2 border-emerald-100"></div>
                     <div class="size-3 rounded-full bg-emerald-100 ml-4"></div>
                     <div class="size-3 rounded-full bg-amber-100 mt-1"></div>
                </div>

                <!-- Photo -->
                <div class="w-[120px] h-[150px] border-[5px] border-amber-500 p-0.5 bg-white mb-6 relative z-10 shadow-sm">
                    @if($siswa->foto)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($siswa->foto) }}" alt="Foto Siswa" class="w-full h-full object-cover" />
                    @else
                        <div class="w-full h-full bg-gray-50 flex items-center justify-center">
                            <flux:icon name="user" class="size-16 text-gray-300" variant="solid" />
                        </div>
                    @endif
                </div>

                <!-- Name and details -->
                <h2 class="font-black text-emerald-600 text-[22px] uppercase tracking-wide text-center leading-tight mb-3 z-10 px-4">
                    {{ $siswa->nama }}
                </h2>
                
                <div class="text-gray-500 text-[13px] text-center z-10 space-y-1 w-full px-6">
                    <div class="flex justify-center items-center gap-2">
                        <span>Kelas :</span>
                        <span class="font-semibold text-gray-700">{{ optional($siswa->kelas)->nama ?? '-' }}</span>
                    </div>
                    <div class="flex justify-center items-center gap-2">
                        <span>TTL :</span>
                        <span class="font-semibold text-gray-700">{{ $siswa->tempat_lahir ?? '-' }}, {{ $siswa->tanggal_lahir ? \Carbon\Carbon::parse($siswa->tanggal_lahir)->format('d-m-Y') : '-' }}</span>
                    </div>
                    <div class="flex justify-center items-center gap-2">
                        <span>Alamat :</span>
                        <span class="font-semibold text-gray-700 truncate max-w-[200px]">{{ $siswa->alamat ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <!-- Bottom Footer (Orange) -->
            <div class="relative bg-amber-500 h-[76px] w-full flex flex-col items-center justify-end pb-3 z-10">
                <div class="w-32 border-t border-white/60 mb-1.5"></div>
                <div class="text-white/90 text-[11px] font-medium tracking-wide">Kepala Sekolah</div>

                <!-- Small teal corner piece at bottom right -->
                <div class="absolute bottom-0 right-0 w-24 h-2 bg-emerald-700"></div>
            </div>
        </div>
        
        <div class="mt-8 text-center px-4">
            <p class="text-xs text-gray-400 leading-relaxed">Kartu digital ini resmi diterbitkan oleh sistem dan berfungsi sebagai identitas siswa yang sah selama masa aktif pendidikan.</p>
        </div>
    </div>
</div>
