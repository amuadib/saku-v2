<div class="bg-gray-50 min-h-screen pb-24">
    <div class="px-6 pt-10 pb-4 bg-white shadow-sm border-b border-gray-100 sticky top-0 z-20">
        <h2 class="font-bold text-xl text-gray-800">Info dan Berita</h2>
        
        <!-- Category Pills -->
        <div class="flex gap-2 mt-5 overflow-x-auto pb-2 scrollbar-hide" style="scrollbar-width: none;">
            <button wire:click="setKategori('Semua')" class="whitespace-nowrap px-4 py-1.5 rounded-full text-sm font-semibold transition {{ $kategori === 'Semua' ? 'bg-emerald-600 text-white shadow-sm' : 'border border-emerald-600 text-emerald-600 hover:bg-emerald-50' }}">Semua</button>
            <button wire:click="setKategori('santri')" class="whitespace-nowrap px-4 py-1.5 rounded-full text-sm font-semibold transition {{ $kategori === 'santri' ? 'bg-emerald-600 text-white shadow-sm' : 'border border-emerald-600 text-emerald-600 hover:bg-emerald-50' }}">santri</button>
            <button wire:click="setKategori('8b')" class="whitespace-nowrap px-4 py-1.5 rounded-full text-sm font-semibold transition {{ $kategori === '8b' ? 'bg-emerald-600 text-white shadow-sm' : 'border border-emerald-600 text-emerald-600 hover:bg-emerald-50' }}">8b</button>
            <button wire:click="setKategori('laundry')" class="whitespace-nowrap px-4 py-1.5 rounded-full text-sm font-semibold transition {{ $kategori === 'laundry' ? 'bg-emerald-600 text-white shadow-sm' : 'border border-emerald-600 text-emerald-600 hover:bg-emerald-50' }}">laundry</button>
        </div>
    </div>
    
    <div class="px-6 pt-6 space-y-4">
        <!-- Sample News Items matching the image -->
        <div class="bg-white rounded-2xl shadow-[0_2px_10px_-4px_rgba(0,0,0,0.1)] border border-gray-100 overflow-hidden cursor-pointer hover:shadow-md transition">
            <div class="flex h-32">
                <div class="w-1/3 min-w-[100px] h-full bg-blue-50 relative overflow-hidden">
                    <img src="https://placehold.co/400x400/3b82f6/white?text=INFO" alt="Berita" class="w-full h-full object-cover">
                </div>
                <div class="w-2/3 p-4 flex flex-col justify-start">
                    <div class="text-[10px] text-gray-400 font-medium mb-1">02-08-2026</div>
                    <div class="font-bold text-sm text-gray-800 mb-1 leading-tight line-clamp-1">Tagihan Bulan Juli 2026</div>
                    <div class="text-[11px] text-gray-500 leading-relaxed line-clamp-3">Assalamu'alaikum. Wr. Wb. Sugeng sonten Bapak/Ibu. Pangapunten wonten kesalahan input tagihan untuk SMAI Bulan Juli 2026, Seharusnya gratis saking Yayasan.</div>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-2xl shadow-[0_2px_10px_-4px_rgba(0,0,0,0.1)] border border-gray-100 overflow-hidden cursor-pointer hover:shadow-md transition">
            <div class="flex h-32">
                <div class="w-1/3 min-w-[100px] h-full bg-emerald-50 relative overflow-hidden">
                    <img src="https://placehold.co/400x400/10b981/white?text=Mondok" alt="Berita" class="w-full h-full object-cover">
                </div>
                <div class="w-2/3 p-4 flex flex-col justify-start">
                    <div class="text-[10px] text-gray-400 font-medium mb-1">10-07-2026</div>
                    <div class="font-bold text-sm text-gray-800 mb-1 leading-tight line-clamp-1">Siap Berangkat Mondok !</div>
                    <div class="text-[11px] text-gray-500 leading-relaxed line-clamp-3">Persiapan keberangkatan mondok santri baru tahun ajaran 2026.</div>
                </div>
            </div>
        </div>
    </div>
</div>
