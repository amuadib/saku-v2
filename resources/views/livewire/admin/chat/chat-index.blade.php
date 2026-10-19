<div class="h-[calc(100vh-8.5rem)] flex flex-col md:flex-row bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-200 dark:border-zinc-800 shadow-sm overflow-hidden relative"
    x-data="{
        scrollToBottom() {
            $nextTick(() => {
                let container = document.getElementById('admin-chat-messages-scroll');
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            });
        },
        lightboxUrl: null
    }"
    x-init="scrollToBottom()"
    x-on:message-sent.window="scrollToBottom()"
    x-on:chat-selected.window="scrollToBottom()"
    wire:poll.visible.5s>

    <!-- ================= LEFT PANEL: CONVERSATIONS LIST ================= -->
    <div class="w-full md:w-80 lg:w-96 border-r border-zinc-200 dark:border-zinc-800 flex flex-col bg-zinc-50/60 dark:bg-zinc-950/40 flex-shrink-0 {{ $selectedSiswaId ? 'hidden md:flex' : 'flex' }}">
        
        <!-- Header & Search -->
        <div class="p-4 border-b border-zinc-200 dark:border-zinc-800 space-y-3 bg-white dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="p-2 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 rounded-xl">
                        <flux:icon name="chat-bubble-oval-left-ellipsis" class="size-5" />
                    </div>
                    <div>
                        <h2 class="font-bold text-zinc-900 dark:text-zinc-100 text-base leading-tight">Chat Siswa</h2>
                        <p class="text-[11px] text-zinc-500">Pesan & bantuan layanan</p>
                    </div>
                </div>

                @if($totalUnread > 0)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-500 text-white shadow-xs">
                        {{ $totalUnread }} baru
                    </span>
                @endif
            </div>

            <!-- Search input -->
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-zinc-400">
                    <flux:icon name="magnifying-glass" class="size-4" />
                </div>
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Cari nama atau NIS siswa..." 
                    class="w-full pl-9 pr-3 py-1.5 text-xs bg-zinc-100 dark:bg-zinc-800 border-none rounded-xl text-zinc-800 dark:text-zinc-200 placeholder-zinc-400 focus:ring-2 focus:ring-emerald-500"
                />
            </div>

            <!-- Filter Unread -->
            <div class="flex items-center justify-between pt-1 text-xs">
                <span class="text-zinc-500 text-[11px]">Filter:</span>
                <button 
                    type="button" 
                    wire:click="$toggle('filterUnreadOnly')" 
                    class="px-2.5 py-1 rounded-lg text-xs font-medium transition {{ $filterUnreadOnly ? 'bg-emerald-600 text-white' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-200' }}"
                >
                    {{ $filterUnreadOnly ? 'Hanya Belum Dibaca ✓' : 'Semua Percakapan' }}
                </button>
            </div>
        </div>

        <!-- Student List -->
        <div class="flex-1 overflow-y-auto divide-y divide-zinc-100 dark:divide-zinc-800/60">
            @forelse($conversations as $conv)
                @php
                    $isSelected = $selectedSiswaId === $conv->id;
                    $hasUnread = ($conv->unread_count ?? 0) > 0;
                    $lastMsg = $conv->latestChatMessage;
                @endphp
                <div 
                    wire:click="selectSiswa('{{ $conv->id }}')" 
                    class="p-3.5 flex items-start gap-3 cursor-pointer transition relative {{ $isSelected ? 'bg-emerald-50/70 dark:bg-emerald-950/30 border-l-4 border-emerald-600' : 'hover:bg-zinc-100/70 dark:hover:bg-zinc-900/60' }}"
                >
                    <!-- Avatar -->
                    <div class="relative flex-shrink-0">
                        <div class="size-11 rounded-full bg-zinc-200 dark:bg-zinc-800 flex items-center justify-center text-zinc-600 dark:text-zinc-300 font-bold overflow-hidden shadow-xs">
                            @if($conv->foto)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($conv->foto) }}" class="size-full object-cover" alt="Foto">
                            @else
                                <span>{{ strtoupper(substr($conv->nama, 0, 2)) }}</span>
                            @endif
                        </div>
                        @if($hasUnread)
                            <span class="absolute top-0 right-0 size-3 rounded-full bg-emerald-500 border-2 border-white dark:border-zinc-900"></span>
                        @endif
                    </div>

                    <!-- Details -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-1 mb-0.5">
                            <h4 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 truncate {{ $hasUnread ? 'text-emerald-700 dark:text-emerald-400' : '' }}">
                                {{ $conv->nama }}
                            </h4>
                            <span class="text-[10px] text-zinc-400 flex-shrink-0">
                                {{ $lastMsg ? $lastMsg->created_at->format('H:i') : '' }}
                            </span>
                        </div>

                        <div class="text-[11px] text-zinc-400 mb-1 flex items-center gap-1.5">
                            <span>NIS: {{ $conv->nis }}</span>
                            <span>&bull;</span>
                            <span class="truncate">{{ optional($conv->kelas)->nama ?? 'Kelas -' }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-2">
                            <p class="text-xs text-zinc-500 dark:text-zinc-400 truncate {{ $hasUnread ? 'font-semibold text-zinc-800 dark:text-zinc-200' : '' }}">
                                @if($lastMsg && $lastMsg->sender_type === 'admin')
                                    <span class="text-emerald-600 font-medium">Anda: </span>
                                @endif
                                @if($lastMsg && $lastMsg->pesan)
                                    {{ $lastMsg->pesan }}
                                @elseif($lastMsg && $lastMsg->hasAttachment())
                                    <span class="italic text-zinc-400">{{ $lastMsg->isImage() ? '📷 Foto/Gambar' : '📄 Dokumen' }}</span>
                                @else
                                    Belum ada pesan
                                @endif
                            </p>

                            @if($hasUnread)
                                <span class="size-5 rounded-full bg-emerald-600 text-white text-[10px] font-bold flex items-center justify-center flex-shrink-0 shadow-xs">
                                    {{ $conv->unread_count }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-zinc-400">
                    <flux:icon name="chat-bubble-left" class="size-8 mx-auto mb-2 opacity-50" />
                    <p class="text-xs">Tidak ada percakapan ditemukan.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- ================= RIGHT PANEL: ACTIVE CHAT THREAD ================= -->
    <div class="flex-1 flex flex-col bg-white dark:bg-zinc-900 h-full {{ !$selectedSiswaId ? 'hidden md:flex' : 'flex' }}">
        @if($selectedSiswa)
            <!-- Chat Header -->
            <div class="p-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between bg-zinc-50/50 dark:bg-zinc-950/30">
                <div class="flex items-center gap-3">
                    <button type="button" wire:click="$set('selectedSiswaId', null)" class="p-1.5 rounded-lg text-zinc-500 hover:bg-zinc-200 dark:hover:bg-zinc-800 md:hidden">
                        <flux:icon name="arrow-left" class="size-5" />
                    </button>

                    <div class="size-11 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 flex items-center justify-center font-bold overflow-hidden shadow-xs">
                        @if($selectedSiswa->foto)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($selectedSiswa->foto) }}" class="size-full object-cover" alt="Foto">
                        @else
                            <span>{{ strtoupper(substr($selectedSiswa->nama, 0, 2)) }}</span>
                        @endif
                    </div>

                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-bold text-sm text-zinc-900 dark:text-zinc-100">
                                {{ $selectedSiswa->nama }}
                            </h3>
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                NIS: {{ $selectedSiswa->nis }}
                            </span>
                        </div>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">
                            {{ config('custom.lembaga.'.$selectedSiswa->lembaga_id.'.nama', 'Lembaga') }} &bull; {{ optional($selectedSiswa->kelas)->nama ?? 'Kelas -' }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.siswa.show', $selectedSiswa->id) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 text-zinc-700 dark:text-zinc-200 transition">
                        <flux:icon name="user" class="size-3.5" />
                        <span>Data Siswa</span>
                    </a>
                </div>
            </div>

            <!-- Chat Message Bubbles -->
            <div id="admin-chat-messages-scroll" class="flex-1 overflow-y-auto p-4 md:p-6 space-y-4 bg-zinc-50/40 dark:bg-zinc-950/20">
                @forelse($activeMessages as $m)
                    @if($m->sender_type === 'admin')
                        <!-- Admin Message (Right) -->
                        <div class="flex flex-col items-end" wire:key="admin-msg-{{ $m->id }}">
                            <div class="flex items-center gap-1.5 mb-1 pr-1">
                                <span class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">{{ $m->sender?->name ?? 'Admin' }}</span>
                            </div>
                            <div class="max-w-[85%] md:max-w-[70%] bg-emerald-600 text-white rounded-2xl rounded-tr-xs p-2.5 shadow-xs text-sm leading-relaxed break-words space-y-2">
                                @if($m->hasAttachment())
                                    @if($m->isImage())
                                        <div class="rounded-xl overflow-hidden bg-black/20 max-w-full">
                                            <button 
                                                type="button" 
                                                @click="lightboxUrl = '{{ $m->attachment_url }}'"
                                                class="block w-full cursor-pointer group relative text-left"
                                            >
                                                <img src="{{ $m->attachment_url }}" class="w-full max-h-64 object-cover rounded-xl transition duration-200 group-hover:scale-[1.02]" alt="{{ $m->attachment_name }}" loading="lazy">
                                                <div class="absolute inset-0 bg-black/25 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs font-semibold gap-1.5 rounded-xl">
                                                    <flux:icon name="arrows-pointing-out" class="size-4" />
                                                    <span>Buka Foto</span>
                                                </div>
                                            </button>
                                        </div>
                                    @elseif($m->isPdf())
                                        <a href="{{ $m->attachment_url }}" target="_blank" class="flex items-center gap-2.5 p-2.5 rounded-xl bg-white/15 hover:bg-white/25 transition border border-white/20 text-white">
                                            <div class="size-10 rounded-lg bg-rose-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                                                <flux:icon name="document-text" class="size-5" />
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-xs font-semibold truncate">{{ $m->attachment_name ?? 'Dokumen PDF' }}</p>
                                                <p class="text-[10px] text-emerald-100">{{ $m->formatted_attachment_size }} &bull; Buka PDF</p>
                                            </div>
                                            <flux:icon name="arrow-top-right-on-square" class="size-4 shrink-0 text-white/80" />
                                        </a>
                                    @else
                                        <a href="{{ $m->attachment_url }}" target="_blank" class="flex items-center gap-2.5 p-2 rounded-xl bg-white/15 hover:bg-white/25 transition border border-white/20 text-white">
                                            <flux:icon name="paper-clip" class="size-5 shrink-0" />
                                            <div class="min-w-0 flex-1">
                                                <p class="text-xs font-semibold truncate">{{ $m->attachment_name ?? 'Berkas' }}</p>
                                                <p class="text-[10px] text-emerald-100">{{ $m->formatted_attachment_size }}</p>
                                            </div>
                                            <flux:icon name="arrow-down-tray" class="size-4 shrink-0" />
                                        </a>
                                    @endif
                                @endif

                                @if($m->pesan)
                                    <div class="{{ $m->hasAttachment() ? 'pt-0.5 px-0.5 text-white/95' : '' }}">
                                        {{ $m->pesan }}
                                    </div>
                                @endif
                            </div>
                            <div class="flex items-center gap-1 mt-1 text-[10px] text-zinc-400 pr-1">
                                <span>{{ $m->created_at->format('d M H:i') }}</span>
                                @if($m->is_read)
                                    <svg class="size-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7M5 13l4 4L19 7"></path></svg>
                                @else
                                    <svg class="size-3 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                @endif
                            </div>
                        </div>
                    @else
                        <!-- Student Message (Left) -->
                        <div class="flex flex-col items-start" wire:key="student-msg-{{ $m->id }}">
                            <div class="flex items-center gap-1.5 mb-1 pl-1">
                                <span class="text-[11px] font-semibold text-zinc-700 dark:text-zinc-300">{{ $selectedSiswa->nama }}</span>
                                <span class="text-[10px] text-zinc-400">Siswa</span>
                            </div>
                            <div class="max-w-[85%] md:max-w-[70%] bg-white dark:bg-zinc-800 text-zinc-800 dark:text-zinc-100 border border-zinc-200 dark:border-zinc-700 rounded-2xl rounded-tl-xs p-2.5 shadow-xs text-sm leading-relaxed break-words space-y-2">
                                @if($m->hasAttachment())
                                    @if($m->isImage())
                                        <div class="rounded-xl overflow-hidden bg-zinc-100 dark:bg-zinc-900 max-w-full">
                                            <button 
                                                type="button" 
                                                @click="lightboxUrl = '{{ $m->attachment_url }}'"
                                                class="block w-full cursor-pointer group relative text-left"
                                            >
                                                <img src="{{ $m->attachment_url }}" class="w-full max-h-64 object-cover rounded-xl transition duration-200 group-hover:scale-[1.02]" alt="{{ $m->attachment_name }}" loading="lazy">
                                                <div class="absolute inset-0 bg-black/25 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs font-semibold gap-1.5 rounded-xl">
                                                    <flux:icon name="arrows-pointing-out" class="size-4" />
                                                    <span>Buka Foto</span>
                                                </div>
                                            </button>
                                        </div>
                                    @elseif($m->isPdf())
                                        <a href="{{ $m->attachment_url }}" target="_blank" class="flex items-center gap-2.5 p-2.5 rounded-xl bg-zinc-50 dark:bg-zinc-900/60 hover:bg-zinc-100 dark:hover:bg-zinc-900 transition border border-zinc-200 dark:border-zinc-700 text-zinc-800 dark:text-zinc-100">
                                            <div class="size-10 rounded-lg bg-rose-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                                                <flux:icon name="document-text" class="size-5" />
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-xs font-semibold truncate">{{ $m->attachment_name ?? 'Dokumen PDF' }}</p>
                                                <p class="text-[10px] text-zinc-500 dark:text-zinc-400">{{ $m->formatted_attachment_size }} &bull; Buka PDF</p>
                                            </div>
                                            <flux:icon name="arrow-top-right-on-square" class="size-4 shrink-0 text-zinc-400" />
                                        </a>
                                    @else
                                        <a href="{{ $m->attachment_url }}" target="_blank" class="flex items-center gap-2.5 p-2 rounded-xl bg-zinc-50 dark:bg-zinc-900/60 hover:bg-zinc-100 dark:hover:bg-zinc-900 transition border border-zinc-200 dark:border-zinc-700 text-zinc-800 dark:text-zinc-100">
                                            <flux:icon name="paper-clip" class="size-5 shrink-0 text-zinc-500" />
                                            <div class="min-w-0 flex-1">
                                                <p class="text-xs font-semibold truncate">{{ $m->attachment_name ?? 'Berkas' }}</p>
                                                <p class="text-[10px] text-zinc-500 dark:text-zinc-400">{{ $m->formatted_attachment_size }}</p>
                                            </div>
                                            <flux:icon name="arrow-down-tray" class="size-4 shrink-0 text-zinc-400" />
                                        </a>
                                    @endif
                                @endif

                                @if($m->pesan)
                                    <div class="{{ $m->hasAttachment() ? 'pt-0.5 px-0.5' : '' }}">
                                        {{ $m->pesan }}
                                    </div>
                                @endif
                            </div>
                            <div class="mt-1 text-[10px] text-zinc-400 pl-1">
                                {{ $m->created_at->format('d M H:i') }}
                            </div>
                        </div>
                    @endif
                @empty
                    <div class="p-8 text-center text-zinc-400">
                        <flux:icon name="chat-bubble-left-right" class="size-10 mx-auto mb-2 opacity-40 text-emerald-500" />
                        <p class="text-sm font-medium">Belum ada riwayat pesan dengan siswa ini.</p>
                        <p class="text-xs text-zinc-400 mt-1">Kirim pesan pertama Anda melalui kolom di bawah.</p>
                    </div>
                @endforelse
            </div>

            <!-- Quick Template Chips -->
            <div class="px-4 py-2 bg-zinc-50 dark:bg-zinc-900/80 border-t border-zinc-200 dark:border-zinc-800 flex items-center gap-2 overflow-x-auto text-xs flex-shrink-0">
                <span class="text-[10px] font-semibold text-zinc-400 uppercase flex-shrink-0">Template Cepat:</span>
                <button type="button" wire:click="sendQuickReply('Pesan sudah kami terima dan sedang diproses oleh staf Tata Usaha.')" class="px-2.5 py-1 rounded-lg bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-600 dark:text-zinc-300 hover:text-emerald-600 text-xs flex-shrink-0 transition">
                    ⚡ Pesan sedang diproses
                </button>
                <button type="button" wire:click="sendQuickReply('Mohon melampirkan foto struk / bukti transfer pembayaran untuk verifikasi.')" class="px-2.5 py-1 rounded-lg bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-600 dark:text-zinc-300 hover:text-emerald-600 text-xs flex-shrink-0 transition">
                    ⚡ Minta bukti transfer
                </button>
                <button type="button" wire:click="sendQuickReply('Silakan datang langsung ke kantor Tata Usaha pada jam operasional sekolah.')" class="px-2.5 py-1 rounded-lg bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-600 dark:text-zinc-300 hover:text-emerald-600 text-xs flex-shrink-0 transition">
                    ⚡ Datang ke Tata Usaha
                </button>
            </div>

            <!-- Input Bar -->
            <div class="bg-white dark:bg-zinc-900 border-t border-zinc-200 dark:border-zinc-800 flex-shrink-0">
                <!-- Attachment Preview Bar -->
                @if($attachment)
                    <div class="px-4 py-2 bg-emerald-50/70 dark:bg-emerald-950/30 border-b border-emerald-100 dark:border-emerald-900 flex items-center justify-between">
                        <div class="flex items-center gap-2.5 min-w-0">
                            @php
                                $isImg = false;
                                $isPdf = false;
                                $tempUrl = null;
                                try {
                                    $mime = $attachment->getMimeType();
                                    $name = $attachment->getClientOriginalName();
                                    $isImg = str_starts_with($mime, 'image/');
                                    $isPdf = $mime === 'application/pdf' || str_ends_with(strtolower($name), '.pdf');
                                    if ($isImg) {
                                        $tempUrl = $attachment->temporaryUrl();
                                    }
                                } catch (\Throwable $e) {}
                            @endphp
                            @if($isImg && $tempUrl)
                                <div class="size-10 rounded-lg overflow-hidden border border-emerald-200 bg-white shrink-0 shadow-xs">
                                    <img src="{{ $tempUrl }}" class="size-full object-cover" alt="Preview Foto">
                                </div>
                            @elseif($isPdf)
                                <div class="size-10 rounded-lg bg-rose-100 border border-rose-200 flex items-center justify-center text-rose-600 shrink-0 shadow-xs">
                                    <flux:icon name="document-text" class="size-5" />
                                </div>
                            @else
                                <div class="size-10 rounded-lg bg-emerald-100 border border-emerald-200 flex items-center justify-center text-emerald-600 shrink-0 shadow-xs">
                                    <flux:icon name="paper-clip" class="size-5" />
                                </div>
                            @endif
                            <div class="min-w-0 text-left">
                                <p class="text-xs font-semibold text-zinc-800 dark:text-zinc-200 truncate max-w-xs">
                                    {{ $attachment->getClientOriginalName() }}
                                </p>
                                <p class="text-[10px] text-emerald-700 dark:text-emerald-400 font-medium">
                                    {{ $isImg ? 'Foto / Gambar' : ($isPdf ? 'Dokumen PDF' : 'Berkas') }} &bull; {{ number_format($attachment->getSize() / 1024, 0) }} KB
                                </p>
                            </div>
                        </div>
                        <button 
                            type="button" 
                            wire:click="cancelAttachment" 
                            class="p-1.5 rounded-full hover:bg-emerald-200/60 text-zinc-400 hover:text-rose-500 transition active:scale-90" 
                            title="Batal Lampiran"
                        >
                            <flux:icon name="x-mark" class="size-4" />
                        </button>
                    </div>
                @endif

                <div wire:loading wire:target="attachment" class="px-4 py-1.5 bg-emerald-50 dark:bg-emerald-950/40 border-b border-emerald-100 dark:border-emerald-900 flex items-center gap-2 text-xs text-emerald-700 dark:text-emerald-300 font-medium">
                    <svg class="animate-spin size-4 text-emerald-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                    </svg>
                    <span>Mengunggah lampiran...</span>
                </div>

                @error('attachment')
                    <div class="px-4 py-1.5 bg-rose-50 dark:bg-rose-950/40 border-b border-rose-100 dark:border-rose-900 text-rose-600 dark:text-rose-400 text-xs font-medium flex items-center gap-1.5">
                        <flux:icon name="exclamation-circle" class="size-4 shrink-0" />
                        <span>{{ $message }}</span>
                    </div>
                @enderror

                <input 
                    type="file" 
                    id="admin-file-input" 
                    wire:model="attachment" 
                    accept="image/*,application/pdf" 
                    class="sr-only" 
                />

                <div class="p-3">
                    <form wire:submit="sendMessage" class="flex items-center gap-2">
                        <label 
                            for="admin-file-input"
                            class="size-10 rounded-xl bg-zinc-100 dark:bg-zinc-800 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 hover:text-emerald-600 dark:hover:text-emerald-400 text-zinc-500 flex items-center justify-center transition shrink-0 active:scale-95 cursor-pointer"
                            title="Lampirkan File / Gambar / PDF"
                        >
                            <flux:icon name="paper-clip" class="size-5 pointer-events-none" />
                        </label>

                        <input 
                            type="text" 
                            wire:model="pesan" 
                            placeholder="{{ $attachment ? 'Beri pesan tambahan (opsional)...' : 'Tulis balasan untuk '.$selectedSiswa->nama.'...' }}" 
                            class="flex-1 bg-zinc-100 dark:bg-zinc-800 px-4 py-2.5 text-sm text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 rounded-2xl border-none focus:ring-2 focus:ring-emerald-500"
                            autocomplete="off"
                        />
                        <flux:button type="submit" variant="primary" icon="paper-airplane" wire:loading.attr="disabled" wire:target="sendMessage,attachment">
                            Kirim
                        </flux:button>
                    </form>
                </div>
            </div>
        @else
            <!-- Empty State when no student selected -->
            <div class="flex-1 flex flex-col items-center justify-center p-8 text-center text-zinc-400">
                <div class="size-20 rounded-3xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-4 shadow-xs">
                    <flux:icon name="chat-bubble-oval-left-ellipsis" class="size-10" />
                </div>
                <h3 class="text-base font-bold text-zinc-800 dark:text-zinc-200">Pusat Pesan & Chat Siswa</h3>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 max-w-sm mt-1 leading-relaxed">
                    Pilih percakapan siswa di panel sebelah kiri untuk membaca dan membalas pesan bantuan secara langsung.
                </p>
            </div>
        @endif
    </div>

    <!-- ================= FULLSCREEN LIGHTBOX (Alpine.js) ================= -->
    <div 
        x-show="lightboxUrl" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 bg-black/95 flex flex-col justify-between p-4"
        style="display: none;"
    >
        <div class="flex items-center justify-between text-white pb-2">
            <span class="text-xs font-medium text-zinc-300">Pratinjau Foto Lampiran</span>
            <div class="flex items-center gap-2">
                <a :href="lightboxUrl" target="_blank" download class="p-2 rounded-full bg-white/20 hover:bg-white/30 text-white transition" title="Unduh Foto">
                    <flux:icon name="arrow-down-tray" class="size-5" />
                </a>
                <button type="button" @click="lightboxUrl = null" class="p-2 rounded-full bg-white/20 hover:bg-white/30 text-white transition" title="Tutup">
                    <flux:icon name="x-mark" class="size-5" />
                </button>
            </div>
        </div>
        <div class="flex-1 flex items-center justify-center p-2 overflow-hidden">
            <img :src="lightboxUrl" class="max-w-full max-h-[85vh] object-contain rounded-xl shadow-2xl" alt="Preview Foto">
        </div>
    </div>
</div>
