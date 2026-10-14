<div class="bg-gray-50 min-h-screen flex flex-col pb-44" 
    x-data="{
        scrollToBottom() {
            $nextTick(() => {
                let el = document.getElementById('chat-scroll-anchor');
                if (el) {
                    el.scrollIntoView({ behavior: 'smooth' });
                }
            });
        }
    }"
    x-init="scrollToBottom()"
    x-on:message-sent.window="scrollToBottom()"
    @if($messages->isNotEmpty()) wire:poll.visible.4s @endif>

    <!-- Top Sticky Header -->
    <div class="px-5 pt-10 pb-4 bg-white/95 backdrop-blur-md shadow-sm border-b border-gray-100 sticky top-0 z-30">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('siswa.dashboard') }}" wire:navigate class="p-2 -ml-2 rounded-full text-gray-500 hover:bg-gray-100 active:scale-95 transition">
                    <flux:icon name="arrow-left" class="size-5" />
                </a>
                
                <div class="relative">
                    <div class="size-11 rounded-full bg-emerald-100 border border-emerald-200 flex items-center justify-center text-emerald-700 font-bold overflow-hidden shadow-xs">
                        @if($siswa && $siswa->lembaga_logo_url)
                            <img src="{{ $siswa->lembaga_logo_url }}" class="size-full object-contain p-1" alt="Logo">
                        @else
                            <flux:icon name="building-office-2" class="size-6 text-emerald-600" />
                        @endif
                    </div>
                    <span class="absolute bottom-0 right-0 size-3 rounded-full bg-emerald-500 border-2 border-white ring-1 ring-emerald-400/40"></span>
                </div>

                <div>
                    <h2 class="font-bold text-[15px] text-gray-800 leading-tight">Layanan & Bantuan</h2>
                    <div class="flex items-center gap-1.5 mt-0.5">
                        <span class="size-1.5 rounded-full bg-emerald-500"></span>
                        <p class="text-[11px] text-emerald-600 font-medium">
                            {{ config('custom.lembaga.'.($siswa?->lembaga_id ?? 1).'.singkatan', 'Admin') }} &bull; Online
                        </p>
                    </div>
                </div>
            </div>

            <div class="text-xs text-gray-400 font-medium hidden sm:block">
                SAKU V2
            </div>
        </div>
    </div>

    <!-- Messages Container -->
    <div class="px-4 py-6 flex-1 space-y-4">
        @if($messages->isEmpty())
            <!-- Welcome Empty State -->
            <div class="bg-white rounded-3xl p-6 shadow-xs border border-gray-100 text-center space-y-4 mt-2">
                <div class="size-14 mx-auto rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <flux:icon name="chat-bubble-left-right" class="size-7" />
                </div>
                <div>
                    <h3 class="font-bold text-gray-800 text-base">Halo, {{ $siswa?->nama ?? 'Siswa' }}! 👋</h3>
                    <p class="text-xs text-gray-500 mt-1 leading-relaxed max-w-xs mx-auto">
                        Ada pertanyaan mengenai tagihan, tabungan, transaksi, atau butuh bantuan administrasi? Silakan kirim pesan ke pihak sekolah di sini.
                    </p>
                </div>

                <!-- Suggestion Chips -->
                <div class="pt-2 border-t border-gray-100 text-left space-y-2">
                    <div class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Pertanyaan Cepat:</div>
                    <div class="flex flex-col gap-1.5">
                        <button type="button" wire:click="sendQuickMessage('Halo Admin, mohon info rincian tagihan saya yang belum dibayar.')" class="w-full text-left px-3.5 py-2.5 rounded-xl bg-gray-50 hover:bg-emerald-50 hover:text-emerald-700 text-gray-600 text-xs transition border border-gray-100">
                            💬 Mohon info rincian tagihan
                        </button>
                        <button type="button" wire:click="sendQuickMessage('Halo Admin, saya ingin menanyakan saldo tabungan dan cara penarikan.')" class="w-full text-left px-3.5 py-2.5 rounded-xl bg-gray-50 hover:bg-emerald-50 hover:text-emerald-700 text-gray-600 text-xs transition border border-gray-100">
                            💰 Cek saldo tabungan & penarikan
                        </button>
                        <button type="button" wire:click="sendQuickMessage('Halo Admin, jam operasional layanan tata usaha buka sampai jam berapa ya?')" class="w-full text-left px-3.5 py-2.5 rounded-xl bg-gray-50 hover:bg-emerald-50 hover:text-emerald-700 text-gray-600 text-xs transition border border-gray-100">
                            🕒 Info jam operasional layanan tata usaha
                        </button>
                    </div>
                </div>
            </div>
        @else
            <!-- Notice Banner -->
            <div class="text-center my-2">
                <span class="inline-block px-3 py-1 rounded-full text-[10px] font-semibold bg-gray-200/70 text-gray-600">
                    Pesan Anda terhubung langsung dengan Admin / Tata Usaha
                </span>
            </div>

            <!-- Chat Bubbles -->
            @foreach($messages as $msg)
                @if($msg->sender_type === 'siswa')
                    <!-- Siswa Bubble (Right) -->
                    <div class="flex flex-col items-end">
                        <div class="max-w-[82%] bg-emerald-600 text-white rounded-2xl rounded-tr-xs px-4 py-2.5 shadow-xs text-sm leading-relaxed break-words">
                            {{ $msg->pesan }}
                        </div>
                        <div class="flex items-center gap-1 mt-1 text-[10px] text-gray-400 pr-1">
                            <span>{{ $msg->created_at->format('H:i') }}</span>
                            @if($msg->is_read)
                                <svg class="size-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7M5 13l4 4L19 7"></path></svg>
                            @else
                                <svg class="size-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            @endif
                        </div>
                    </div>
                @else
                    <!-- Admin Bubble (Left) -->
                    <div class="flex flex-col items-start">
                        <div class="flex items-center gap-1.5 mb-1 pl-1">
                            <span class="text-[11px] font-semibold text-emerald-700">{{ $msg->sender?->name ?? 'Admin' }}</span>
                            <span class="px-1.5 py-0.2 rounded text-[9px] bg-emerald-100 text-emerald-800 font-bold">Resmi</span>
                        </div>
                        <div class="max-w-[82%] bg-white text-gray-800 border border-gray-200/80 rounded-2xl rounded-tl-xs px-4 py-2.5 shadow-xs text-sm leading-relaxed break-words">
                            {{ $msg->pesan }}
                        </div>
                        <div class="mt-1 text-[10px] text-gray-400 pl-1">
                            {{ $msg->created_at->format('H:i') }}
                        </div>
                    </div>
                @endif
            @endforeach

            <div id="chat-scroll-anchor" class="h-2"></div>
        @endif
    </div>

    <!-- Fixed Bottom Input Bar (Positioned above bottom nav) -->
    <div class="fixed bottom-[74px] left-0 right-0 max-w-md mx-auto px-4 z-40">
        <form wire:submit="sendMessage" class="bg-white/95 backdrop-blur-md rounded-2xl p-2 shadow-lg border border-gray-200/80 flex items-center gap-2">
            <input 
                type="text" 
                wire:model="pesan" 
                placeholder="Tulis pesan ke admin..." 
                class="flex-1 bg-transparent px-3 py-2 text-sm text-gray-800 placeholder-gray-400 focus:outline-none"
                autocomplete="off"
            />
            <button 
                type="submit" 
                wire:loading.attr="disabled"
                wire:target="sendMessage"
                class="size-10 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white flex items-center justify-center transition-all disabled:opacity-50 active:scale-95 shadow-md shadow-emerald-600/30 flex-shrink-0"
                title="Kirim Pesan"
            >
                <span wire:loading.remove wire:target="sendMessage">
                    <flux:icon name="paper-airplane" class="size-4" />
                </span>
                <span wire:loading wire:target="sendMessage">
                    <svg class="animate-spin size-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                    </svg>
                </span>
            </button>
        </form>
    </div>
</div>
