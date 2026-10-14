<div class="bg-gray-50 min-h-screen pb-24">
    <div class="px-6 pt-12 pb-4">
        <h2 class="font-bold text-2xl text-gray-800 mb-6">Profil</h2>
        
        <!-- Profile Card -->
        <a href="{{ route('siswa.profil.detail') }}" wire:navigate class="bg-white rounded-3xl p-5 shadow-sm border border-gray-100 flex items-center justify-between mb-8 relative overflow-hidden group hover:border-emerald-200 hover:shadow-md transition">
            <div class="absolute right-0 top-0 w-32 h-32 bg-emerald-50 rounded-full blur-3xl opacity-60 -mr-10 -mt-10 pointer-events-none"></div>
            
            <div class="flex items-center gap-4 relative z-10 flex-1 min-w-0">
                <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center text-gray-400 overflow-hidden shadow-sm flex-shrink-0">
                    @if($siswa?->foto)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($siswa->foto) }}" class="w-full h-full object-cover" alt="Foto Siswa">
                    @else
                        <img src="{{ asset($siswa?->jenis_kelamin === 'p' ? 'images/girl.jpg' : 'images/boy.jpg') }}" class="w-full h-full object-cover" alt="Foto Default">
                    @endif
                </div>
                <div class="overflow-hidden flex-1 min-w-0">
                    <div class="font-bold text-[17px] text-gray-800 leading-tight truncate">{{ $siswa?->nama ?? auth()->user()->username ?? 'Siswa' }}</div>
                    <div class="text-[12px] text-gray-500 mb-0.5 truncate">{{ config('custom.lembaga.'.($siswa?->lembaga_id ?? 1).'.nama') }}</div>
                    <div class="text-[12px] text-gray-500 truncate">{{ $siswa?->nis }} {{ $siswa?->nisn ? '/ ' . $siswa->nisn : '' }}</div>
                </div>
            </div>

            <div class="text-gray-400 group-hover:text-emerald-600 transition flex-shrink-0 ml-3">
                <flux:icon name="chevron-right" class="size-5" />
            </div>
        </a>

        <!-- Menu List -->
        <div class="space-y-1">
            <a href="{{ route('siswa.profil.detail') }}" wire:navigate class="flex items-center justify-between px-2 py-4 border-b border-gray-100 hover:bg-emerald-50/50 transition rounded-xl group">
                <div class="flex items-center gap-4">
                    <div class="text-gray-400 group-hover:text-emerald-600 transition">
                        <flux:icon name="user" class="size-6" variant="outline" />
                    </div>
                    <div>
                        <div class="font-medium text-gray-700 text-sm group-hover:text-emerald-700 transition">Profil Siswa</div>
                        <div class="text-[11px] text-gray-400">Lihat & edit data diri lengkap</div>
                    </div>
                </div>
                <flux:icon name="chevron-right" class="size-4 text-gray-400 group-hover:text-emerald-600 transition" />
            </a>

            <button type="button" wire:click="confirmRegenerate" class="w-full flex items-center justify-between px-2 py-4 border-b border-gray-100 hover:bg-emerald-50/50 transition rounded-xl group text-left">
                <div class="flex items-center gap-4">
                    <div class="text-gray-400 group-hover:text-emerald-600 transition">
                        <flux:icon name="arrow-path" class="size-6" variant="outline" />
                    </div>
                    <div>
                        <div class="font-medium text-gray-700 text-sm group-hover:text-emerald-700 transition">Buat ulang link Login</div>
                        <div class="text-[11px] text-gray-400">Buat ulang link login dan masuk kembali</div>
                    </div>
                </div>
                <flux:icon name="chevron-right" class="size-4 text-gray-400 group-hover:text-emerald-600 transition" />
            </button>
            
            <a href="#" class="flex items-center gap-4 px-2 py-4 border-b border-gray-100 hover:bg-emerald-50/50 transition rounded-xl group">
                <div class="text-gray-400 group-hover:text-emerald-600 transition">
                    <flux:icon name="book-open" class="size-6" variant="outline" />
                </div>
                <div class="font-medium text-gray-700 text-sm group-hover:text-emerald-700 transition">FAQ</div>
            </a>
            
            <a href="#" class="flex items-center gap-4 px-2 py-4 border-b border-gray-100 hover:bg-emerald-50/50 transition rounded-xl group">
                <div class="text-gray-400 group-hover:text-emerald-600 transition">
                    <flux:icon name="globe-alt" class="size-6" variant="outline" />
                </div>
                <div class="font-medium text-gray-700 text-sm group-hover:text-emerald-700 transition">Ganti Bahasa</div>
            </a>
            
            <!-- Logout Form -->
            <form method="POST" action="{{ route('logout') }}" class="block">
                @csrf
                <button type="submit" class="w-full flex items-center gap-4 px-2 py-4 hover:bg-red-50/80 transition rounded-xl group mt-1">
                    <div class="text-gray-400 group-hover:text-red-500 transition">
                        <flux:icon name="arrow-right-start-on-rectangle" class="size-6" variant="outline" />
                    </div>
                    <div class="font-medium text-gray-700 text-sm group-hover:text-red-600 transition">Keluar</div>
                </button>
            </form>
        </div>
    </div>

    <!-- Modal Konfirmasi Regenerate Magic Link -->
    <flux:modal wire:model="showConfirmModal" class="md:w-[420px] space-y-6">
        <div>
            <flux:heading size="lg">Buat ulang link Login</flux:heading>
            <flux:subheading class="mt-2">
                Apakah Anda yakin ingin membuat ulang link login? Link dan sesi lama akan langsung hangus dan Anda akan dialihkan ke link yang baru.
            </flux:subheading>
        </div>

        <div class="flex gap-2 justify-end mt-6">
            <flux:button wire:click="$set('showConfirmModal', false)" variant="ghost">
                Tidak
            </flux:button>
            <flux:button wire:click="regenerateAndRedirect" variant="danger" icon="arrow-path">
                Ya, Regenerate
            </flux:button>
        </div>
    </flux:modal>
</div>
