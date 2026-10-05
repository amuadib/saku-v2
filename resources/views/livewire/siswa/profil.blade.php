<div class="bg-gray-50 min-h-screen pb-24">
    <div class="px-6 pt-12 pb-4">
        <h2 class="font-bold text-2xl text-gray-800 mb-6">Profil</h2>
        
        <!-- Profile Card -->
        <div class="bg-white rounded-3xl p-5 shadow-sm border border-gray-100 flex items-center justify-between mb-8 relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-32 h-32 bg-emerald-50 rounded-full blur-3xl opacity-60 -mr-10 -mt-10 pointer-events-none"></div>
            
            <div class="flex items-center gap-4 relative z-10 w-full">
                <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center text-gray-400 overflow-hidden shadow-sm flex-shrink-0">
                    @if($siswa->foto)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($siswa->foto) }}" class="w-full h-full object-cover" alt="Foto Siswa">
                    @else
                        <img src="{{ asset($siswa->jenis_kelamin === 'p' ? 'girl.jpg' : 'boy.jpg') }}" class="w-full h-full object-cover" alt="Foto Default">
                    @endif
                </div>
                <div class="overflow-hidden">
                    <div class="font-bold text-[17px] text-gray-800 leading-tight truncate">{{ $siswa->nama ?? auth()->user()->username ?? 'Siswa' }}</div>
                    <div class="text-[12px] text-gray-500 mb-0.5 truncate">{{ config('custom.lembaga.'.$siswa->lembaga_id) }}</div>
                    <div class="text-[12px] text-gray-500 truncate">{{ $siswa->nisn ?? '-' }}</div>
                </div>
            </div>
        </div>

        <!-- Menu List -->
        <div class="space-y-1">
            <a href="#" class="flex items-center gap-4 px-2 py-4 border-b border-gray-100 hover:bg-emerald-50/50 transition rounded-xl group">
                <div class="text-gray-400 group-hover:text-emerald-600 transition">
                    <flux:icon name="user" class="size-6" variant="outline" />
                </div>
                <div class="font-medium text-gray-700 text-sm group-hover:text-emerald-700 transition">Profil</div>
            </a>
            
            <a href="#" class="flex items-center gap-4 px-2 py-4 border-b border-gray-100 hover:bg-emerald-50/50 transition rounded-xl group">
                <div class="text-gray-400 group-hover:text-emerald-600 transition">
                    <flux:icon name="lock-closed" class="size-6" variant="outline" />
                </div>
                <div class="font-medium text-gray-700 text-sm group-hover:text-emerald-700 transition">Ubah Kata Sandi</div>
            </a>
            
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
</div>
