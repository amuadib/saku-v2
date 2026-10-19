<div class="bg-gray-50 flex-1 flex flex-col h-full min-h-0 overflow-hidden relative" 
    x-data="{
        scrollToBottom() {
            this.$nextTick(() => {
                const container = document.getElementById('chat-messages-container');
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            });
        },
        showCameraModal: false,
        cameraStream: null,
        capturedDataUrl: null,
        facingMode: 'environment',
        cameraLoading: false,
        cameraError: null,
        lightboxUrl: null,

        async openCamera() {
            this.capturedDataUrl = null;
            this.cameraError = null;
            this.showCameraModal = true;
            await this.startStream();
        },

        async startStream() {
            this.cameraLoading = true;
            this.cameraError = null;
            this.stopStream();
            try {
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    throw new Error('Kamera langsung di browser tidak didukung atau memerlukan koneksi aman (HTTPS).');
                }
                const constraints = {
                    video: {
                        facingMode: { ideal: this.facingMode },
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    },
                    audio: false
                };
                this.cameraStream = await navigator.mediaDevices.getUserMedia(constraints);
                this.$nextTick(() => {
                    const videoEl = this.$refs.videoElement;
                    if (videoEl) {
                        videoEl.srcObject = this.cameraStream;
                        videoEl.play().catch(e => console.warn(e));
                    }
                });
            } catch (err) {
                console.error('Camera error:', err);
                this.cameraError = 'Kamera langsung tidak dapat diakses (' + (err.message || 'Izin ditolak') + '). Anda dapat menggunakan opsi kamera ponsel di bawah.';
            } finally {
                this.cameraLoading = false;
            }
        },

        flipCamera() {
            this.facingMode = this.facingMode === 'user' ? 'environment' : 'user';
            this.startStream();
        },

        takeSnapshot() {
            const video = this.$refs.videoElement;
            const canvas = this.$refs.canvasElement;
            if (!video || !canvas) return;

            canvas.width = video.videoWidth || 640;
            canvas.height = video.videoHeight || 480;
            const ctx = canvas.getContext('2d');
            
            if (this.facingMode === 'user') {
                ctx.translate(canvas.width, 0);
                ctx.scale(-1, 1);
            }
            
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            this.capturedDataUrl = canvas.toDataURL('image/jpeg', 0.92);
            this.stopStream();
        },

        retakePhoto() {
            this.capturedDataUrl = null;
            this.startStream();
        },

        usePhoto() {
            if (!this.capturedDataUrl) return;

            fetch(this.capturedDataUrl)
                .then(res => res.blob())
                .then(blob => {
                    const file = new File([blob], 'foto_kamera_' + Date.now() + '.jpg', { type: 'image/jpeg' });
                    $wire.upload('attachment', file, () => {
                        this.closeCamera();
                        this.scrollToBottom();
                    }, (err) => {
                        console.error('Upload error:', err);
                        alert('Gagal mengunggah foto. Silakan coba lagi.');
                    });
                });
        },

        stopStream() {
            if (this.cameraStream) {
                this.cameraStream.getTracks().forEach(track => track.stop());
                this.cameraStream = null;
            }
        },

        closeCamera() {
            this.stopStream();
            this.showCameraModal = false;
            this.capturedDataUrl = null;
            this.cameraError = null;
        }
    }"
    x-init="scrollToBottom()"
    x-on:message-sent.window="scrollToBottom()"
    @if($messages->isNotEmpty()) wire:poll.visible.4s @endif>

    <!-- Top Sticky Header -->
    <div class="px-4 py-3 bg-white/95 backdrop-blur-md shadow-xs border-b border-gray-100 shrink-0 z-30">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2.5 min-w-0">
                <a href="{{ route('siswa.dashboard') }}" wire:navigate class="p-2 -ml-1 rounded-full text-gray-500 hover:bg-gray-100 active:scale-95 transition shrink-0" title="Kembali ke Beranda">
                    <flux:icon name="arrow-left" class="size-5" />
                </a>
                
                <div class="relative shrink-0">
                    <div class="w-10 h-10 min-w-10 min-h-10 rounded-full bg-emerald-100 border border-emerald-200 flex items-center justify-center text-emerald-700 font-bold overflow-hidden shadow-xs shrink-0" style="width: 40px; height: 40px; min-width: 40px; min-height: 40px;">
                        @if($siswa && $siswa->lembaga_logo_url)
                            <img src="{{ $siswa->lembaga_logo_url }}" class="w-full h-full object-contain p-1" style="width: 100%; height: 100%; max-width: 40px; max-height: 40px; object-fit: contain;" alt="Logo Lembaga">
                        @else
                            <flux:icon name="building-office-2" class="size-5 text-emerald-600" />
                        @endif
                    </div>
                    <span class="absolute bottom-0 right-0 size-2.5 rounded-full bg-emerald-500 border-2 border-white ring-1 ring-emerald-400/40"></span>
                </div>

                <div class="min-w-0">
                    <h2 class="font-bold text-[14px] text-gray-800 leading-tight truncate">Layanan & Bantuan</h2>
                    <div class="flex items-center gap-1.5 mt-0.5">
                        <span class="size-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                        <p class="text-[11px] text-emerald-600 font-medium truncate">
                            {{ config('custom.lembaga.'.($siswa?->lembaga_id ?? 1).'.singkatan', 'Admin') }} &bull; Online
                        </p>
                    </div>
                </div>
            </div>

            <div class="text-[11px] font-semibold text-gray-400 px-2 py-0.5 bg-gray-100 rounded-md shrink-0">
                SAKU V2
            </div>
        </div>
    </div>

    <!-- Messages Container (Scrollable) -->
    <div id="chat-messages-container" class="flex-1 overflow-y-auto px-4 py-4 space-y-3.5 overscroll-contain min-h-0">
        @if($messages->isEmpty())
            <!-- Welcome Empty State -->
            <div class="bg-white rounded-3xl p-5 shadow-xs border border-gray-100 text-center space-y-3.5 my-auto">
                <div class="size-12 mx-auto rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <flux:icon name="chat-bubble-left-right" class="size-6" />
                </div>
                <div>
                    <h3 class="font-bold text-gray-800 text-sm">Halo, {{ $siswa?->nama ?? 'Siswa' }}! 👋</h3>
                    <p class="text-xs text-gray-500 mt-1 leading-relaxed max-w-xs mx-auto">
                        Ada pertanyaan mengenai tagihan, tabungan, transaksi, atau butuh bantuan administrasi? Silakan kirim pesan atau berkas/foto ke pihak sekolah di sini.
                    </p>
                </div>

                <!-- Suggestion Chips -->
                <div class="pt-2 border-t border-gray-100 text-left space-y-2">
                    <div class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Pertanyaan Cepat:</div>
                    <div class="flex flex-col gap-1.5">
                        <button type="button" wire:click="sendQuickMessage('Halo Admin, mohon info rincian tagihan saya yang belum dibayar.')" class="w-full text-left px-3 py-2 rounded-xl bg-gray-50 hover:bg-emerald-50 hover:text-emerald-700 text-gray-600 text-xs transition border border-gray-100">
                            💬 Mohon info rincian tagihan
                        </button>
                        <button type="button" wire:click="sendQuickMessage('Halo Admin, saya ingin menanyakan saldo tabungan dan cara penarikan.')" class="w-full text-left px-3 py-2 rounded-xl bg-gray-50 hover:bg-emerald-50 hover:text-emerald-700 text-gray-600 text-xs transition border border-gray-100">
                            💰 Cek saldo tabungan & penarikan
                        </button>
                        <button type="button" wire:click="sendQuickMessage('Halo Admin, jam operasional layanan tata usaha buka sampai jam berapa ya?')" class="w-full text-left px-3 py-2 rounded-xl bg-gray-50 hover:bg-emerald-50 hover:text-emerald-700 text-gray-600 text-xs transition border border-gray-100">
                            🕒 Info jam operasional layanan tata usaha
                        </button>
                    </div>
                </div>
            </div>
        @else
            <!-- Notice Banner -->
            <div class="text-center my-1">
                <span class="inline-block px-3 py-1 rounded-full text-[10px] font-semibold bg-gray-200/70 text-gray-600">
                    Pesan Anda terhubung langsung dengan Admin / Tata Usaha
                </span>
            </div>

            <!-- Chat Bubbles -->
            @foreach($messages as $msg)
                @if($msg->sender_type === 'siswa')
                    <!-- Siswa Bubble (Right) -->
                    <div class="flex flex-col items-end" wire:key="msg-{{ $msg->id }}">
                        <div class="max-w-[85%] sm:max-w-[78%] bg-emerald-600 text-white rounded-2xl rounded-tr-xs p-2.5 shadow-xs text-sm leading-relaxed break-words space-y-2">
                            @if($msg->hasAttachment())
                                @if($msg->isImage())
                                    <div class="rounded-xl overflow-hidden bg-black/20 max-w-full">
                                        <button 
                                            type="button" 
                                            @click="lightboxUrl = '{{ $msg->attachment_url }}'"
                                            class="block w-full cursor-pointer group relative text-left"
                                        >
                                            <img src="{{ $msg->attachment_url }}" class="w-full max-h-60 object-cover rounded-xl transition duration-200 group-hover:scale-[1.02]" alt="{{ $msg->attachment_name }}" loading="lazy">
                                            <div class="absolute inset-0 bg-black/25 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs font-semibold gap-1.5 rounded-xl">
                                                <flux:icon name="arrows-pointing-out" class="size-4" />
                                                <span>Buka Foto</span>
                                            </div>
                                        </button>
                                    </div>
                                @elseif($msg->isPdf())
                                    <a href="{{ $msg->attachment_url }}" target="_blank" class="flex items-center gap-2.5 p-2.5 rounded-xl bg-white/15 hover:bg-white/25 transition border border-white/20 text-white">
                                        <div class="size-10 rounded-lg bg-rose-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                                            <flux:icon name="document-text" class="size-5" />
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-xs font-semibold truncate">{{ $msg->attachment_name ?? 'Dokumen PDF' }}</p>
                                            <p class="text-[10px] text-emerald-100">{{ $msg->formatted_attachment_size }} &bull; Buka PDF</p>
                                        </div>
                                        <flux:icon name="arrow-top-right-on-square" class="size-4 shrink-0 text-white/80" />
                                    </a>
                                @else
                                    <a href="{{ $msg->attachment_url }}" target="_blank" class="flex items-center gap-2.5 p-2 rounded-xl bg-white/15 hover:bg-white/25 transition border border-white/20 text-white">
                                        <flux:icon name="paper-clip" class="size-5 shrink-0" />
                                        <div class="min-w-0 flex-1">
                                            <p class="text-xs font-semibold truncate">{{ $msg->attachment_name ?? 'Berkas' }}</p>
                                            <p class="text-[10px] text-emerald-100">{{ $msg->formatted_attachment_size }}</p>
                                        </div>
                                        <flux:icon name="arrow-down-tray" class="size-4 shrink-0" />
                                    </a>
                                @endif
                            @endif

                            @if($msg->pesan)
                                <div class="{{ $msg->hasAttachment() ? 'pt-0.5 px-0.5 text-white/95' : '' }}">
                                    {{ $msg->pesan }}
                                </div>
                            @endif
                        </div>
                        <div class="flex items-center gap-1 mt-1 text-[10px] text-gray-400 pr-1">
                            <span>{{ $msg->created_at->format('H:i') }}</span>
                            @if($msg->is_read)
                                <svg class="size-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7M5 13l4 4L19 7"></path></svg>
                            @else
                                <svg class="size-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7M5 13l4 4L19 7"></path></svg>
                            @endif
                        </div>
                    </div>
                @else
                    <!-- Admin Bubble (Left) -->
                    <div class="flex flex-col items-start" wire:key="msg-{{ $msg->id }}">
                        <div class="flex items-center gap-1.5 mb-1 pl-1">
                            <span class="text-[11px] font-semibold text-emerald-700">{{ $msg->sender?->name ?? 'Admin' }}</span>
                            <span class="px-1.5 py-0.2 rounded text-[9px] bg-emerald-100 text-emerald-800 font-bold">Resmi</span>
                        </div>
                        <div class="max-w-[85%] sm:max-w-[78%] bg-white text-gray-800 border border-gray-200/80 rounded-2xl rounded-tl-xs p-2.5 shadow-xs text-sm leading-relaxed break-words space-y-2">
                            @if($msg->hasAttachment())
                                @if($msg->isImage())
                                    <div class="rounded-xl overflow-hidden bg-gray-100 max-w-full">
                                        <button 
                                            type="button" 
                                            @click="lightboxUrl = '{{ $msg->attachment_url }}'"
                                            class="block w-full cursor-pointer group relative text-left"
                                        >
                                            <img src="{{ $msg->attachment_url }}" class="w-full max-h-60 object-cover rounded-xl transition duration-200 group-hover:scale-[1.02]" alt="{{ $msg->attachment_name }}" loading="lazy">
                                            <div class="absolute inset-0 bg-black/25 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs font-semibold gap-1.5 rounded-xl">
                                                <flux:icon name="arrows-pointing-out" class="size-4" />
                                                <span>Buka Foto</span>
                                            </div>
                                        </button>
                                    </div>
                                @elseif($msg->isPdf())
                                    <a href="{{ $msg->attachment_url }}" target="_blank" class="flex items-center gap-2.5 p-2.5 rounded-xl bg-gray-50 hover:bg-gray-100 transition border border-gray-200 text-gray-800">
                                        <div class="size-10 rounded-lg bg-rose-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                                            <flux:icon name="document-text" class="size-5" />
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-xs font-semibold truncate text-gray-900">{{ $msg->attachment_name ?? 'Dokumen PDF' }}</p>
                                            <p class="text-[10px] text-gray-500">{{ $msg->formatted_attachment_size }} &bull; Buka PDF</p>
                                        </div>
                                        <flux:icon name="arrow-top-right-on-square" class="size-4 shrink-0 text-gray-400" />
                                    </a>
                                @else
                                    <a href="{{ $msg->attachment_url }}" target="_blank" class="flex items-center gap-2.5 p-2 rounded-xl bg-gray-50 hover:bg-gray-100 transition border border-gray-200 text-gray-800">
                                        <flux:icon name="paper-clip" class="size-5 shrink-0 text-gray-500" />
                                        <div class="min-w-0 flex-1">
                                            <p class="text-xs font-semibold truncate text-gray-900">{{ $msg->attachment_name ?? 'Berkas' }}</p>
                                            <p class="text-[10px] text-gray-500">{{ $msg->formatted_attachment_size }}</p>
                                        </div>
                                        <flux:icon name="arrow-down-tray" class="size-4 shrink-0 text-gray-400" />
                                    </a>
                                @endif
                            @endif

                            @if($msg->pesan)
                                <div class="{{ $msg->hasAttachment() ? 'pt-0.5 px-0.5' : '' }}">
                                    {{ $msg->pesan }}
                                </div>
                            @endif
                        </div>
                        <div class="mt-1 text-[10px] text-gray-400 pl-1">
                            {{ $msg->created_at->format('H:i') }}
                        </div>
                    </div>
                @endif
            @endforeach

            <div id="chat-scroll-anchor" class="h-1"></div>
        @endif
    </div>

    <!-- Bottom Input Bar -->
    <div class="shrink-0 bg-white border-t border-gray-200/80 z-30">
        
        <!-- Attachment Preview Bar (If attachment chosen) -->
        @if($attachment)
            <div class="px-3 pt-2.5 pb-2 bg-emerald-50/70 border-b border-emerald-100 flex items-center justify-between">
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
                        <div class="size-11 rounded-lg overflow-hidden border border-emerald-200 bg-white shrink-0 shadow-xs">
                            <img src="{{ $tempUrl }}" class="size-full object-cover" alt="Preview Foto">
                        </div>
                    @elseif($isPdf)
                        <div class="size-11 rounded-lg bg-rose-100 border border-rose-200 flex items-center justify-center text-rose-600 shrink-0 shadow-xs">
                            <flux:icon name="document-text" class="size-6" />
                        </div>
                    @else
                        <div class="size-11 rounded-lg bg-emerald-100 border border-emerald-200 flex items-center justify-center text-emerald-600 shrink-0 shadow-xs">
                            <flux:icon name="paper-clip" class="size-6" />
                        </div>
                    @endif
                    <div class="min-w-0 text-left">
                        <p class="text-xs font-semibold text-gray-800 truncate max-w-[200px] sm:max-w-xs">
                            {{ $attachment->getClientOriginalName() }}
                        </p>
                        <p class="text-[10px] text-emerald-700 font-medium flex items-center gap-1">
                            <span class="size-1.5 rounded-full bg-emerald-500"></span>
                            {{ $isImg ? 'Foto / Gambar' : ($isPdf ? 'Dokumen PDF' : 'Berkas') }} &bull; {{ number_format($attachment->getSize() / 1024, 0) }} KB
                        </p>
                    </div>
                </div>
                <button 
                    type="button" 
                    wire:click="cancelAttachment" 
                    class="p-1.5 rounded-full hover:bg-emerald-200/60 text-gray-400 hover:text-red-500 transition active:scale-90" 
                    title="Batal Lampiran"
                >
                    <flux:icon name="x-mark" class="size-4" />
                </button>
            </div>
        @endif

        <!-- Livewire Upload Loading Indicator -->
        <div wire:loading wire:target="attachment" class="px-3 py-1.5 bg-emerald-50 border-b border-emerald-100 flex items-center gap-2 text-xs text-emerald-700 font-medium">
            <svg class="animate-spin size-4 text-emerald-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
            </svg>
            <span>Sedang memproses & mengunggah berkas...</span>
        </div>

        @error('attachment')
            <div class="px-3 py-1.5 bg-rose-50 border-b border-rose-100 text-rose-600 text-xs font-medium flex items-center gap-1.5">
                <flux:icon name="exclamation-circle" class="size-4 shrink-0" />
                <span>{{ $message }}</span>
            </div>
        @enderror

        <!-- Hidden Native File Inputs (sr-only, fully accessible & native click target) -->
        <!-- 1. Document & Image File Picker -->
        <input 
            type="file" 
            id="chat-file-input" 
            wire:model="attachment" 
            accept="image/*,application/pdf" 
            class="sr-only" 
        />
        <!-- 2. Mobile Native Camera Direct Trigger -->
        <input 
            type="file" 
            id="chat-camera-native" 
            wire:model="attachment" 
            accept="image/*" 
            capture="environment" 
            class="sr-only" 
        />

        <div class="px-3 py-2">
            <form wire:submit="sendMessage" class="flex items-center gap-2">
                
                <!-- Tombol 1: Upload File (PDF / Gambar) -->
                <label 
                    for="chat-file-input" 
                    class="size-10 rounded-xl bg-gray-100 hover:bg-emerald-50 hover:text-emerald-700 text-gray-500 flex items-center justify-center transition active:scale-95 cursor-pointer shrink-0"
                    title="Upload File (PDF / Gambar)"
                >
                    <flux:icon name="paper-clip" class="size-5 pointer-events-none" />
                </label>

                <!-- Tombol 2: Ambil Foto Kamera -->
                <button 
                    type="button" 
                    @click="openCamera()"
                    class="size-10 rounded-xl bg-gray-100 hover:bg-emerald-50 hover:text-emerald-700 text-gray-500 flex items-center justify-center transition active:scale-95 shrink-0"
                    title="Ambil Foto dari Kamera"
                >
                    <flux:icon name="camera" class="size-5 pointer-events-none" />
                </button>

                <!-- Text Input -->
                <input 
                    type="text" 
                    wire:model="pesan" 
                    @focus="scrollToBottom()"
                    placeholder="{{ $attachment ? 'Beri keterangan (opsional)...' : 'Tulis pesan ke admin...' }}" 
                    class="flex-1 min-w-0 bg-gray-100 hover:bg-gray-50 focus:bg-white rounded-xl px-3.5 py-2 text-sm text-gray-800 placeholder-gray-400 border border-transparent focus:border-emerald-500 focus:outline-none transition shadow-inner"
                    autocomplete="off"
                />

                <!-- Submit Button -->
                <button 
                    type="submit" 
                    wire:loading.attr="disabled"
                    wire:target="sendMessage,attachment"
                    class="size-10 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white flex items-center justify-center transition-all disabled:opacity-50 active:scale-95 shadow-md shadow-emerald-600/30 shrink-0"
                    title="Kirim Pesan"
                >
                    <span wire:loading.remove wire:target="sendMessage,attachment">
                        <flux:icon name="paper-airplane" class="size-4 pointer-events-none" />
                    </span>
                    <span wire:loading wire:target="sendMessage,attachment">
                        <svg class="animate-spin size-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                        </svg>
                    </span>
                </button>
            </form>
        </div>
    </div>

    <!-- ================= CAMERA MODAL (Alpine.js) ================= -->
    <div 
        x-show="showCameraModal" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 bg-black/95 flex flex-col justify-between p-4 max-w-md mx-auto"
        style="display: none;"
    >
        <!-- Modal Top Bar -->
        <div class="flex items-center justify-between text-white pb-2 z-10">
            <div class="flex items-center gap-2">
                <flux:icon name="camera" class="size-5 text-emerald-400" />
                <span class="font-bold text-sm">Ambil Foto Kamera</span>
            </div>
            <div class="flex items-center gap-2">
                <button 
                    type="button" 
                    x-show="!capturedDataUrl && !cameraError" 
                    @click="flipCamera()" 
                    class="p-2 rounded-full bg-white/20 hover:bg-white/30 text-white transition active:scale-95"
                    title="Putar Kamera Depan/Belakang"
                >
                    <flux:icon name="arrow-path" class="size-5" />
                </button>
                <button 
                    type="button" 
                    @click="closeCamera()" 
                    class="p-2 rounded-full bg-white/20 hover:bg-white/30 text-white transition active:scale-95"
                    title="Tutup Kamera"
                >
                    <flux:icon name="x-mark" class="size-5" />
                </button>
            </div>
        </div>

        <!-- Camera Viewfinder / Preview Container -->
        <div class="flex-1 flex items-center justify-center relative overflow-hidden rounded-2xl bg-black/80 my-2">
            <!-- Loading Spinner -->
            <div x-show="cameraLoading" class="absolute inset-0 flex flex-col items-center justify-center text-white/80 gap-3">
                <svg class="animate-spin size-8 text-emerald-400" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                </svg>
                <span class="text-xs">Mengaktifkan kamera...</span>
            </div>

            <!-- Error State -->
            <div x-show="cameraError" class="p-6 text-center text-white max-w-sm space-y-4" style="display: none;">
                <div class="size-14 mx-auto rounded-full bg-rose-500/20 text-rose-400 flex items-center justify-center">
                    <flux:icon name="exclamation-triangle" class="size-7" />
                </div>
                <div>
                    <h4 class="font-bold text-base">Akses Kamera Bermasalah</h4>
                    <p class="text-xs text-gray-300 mt-1.5 leading-relaxed" x-text="cameraError"></p>
                </div>
                <div class="pt-2 flex flex-col gap-2">
                    <label 
                        for="chat-camera-native" 
                        @click="closeCamera()" 
                        class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold transition cursor-pointer text-center block"
                    >
                        Gunakan Kamera Bawaan Ponsel
                    </label>
                    <label 
                        for="chat-file-input" 
                        @click="closeCamera()" 
                        class="px-4 py-2 rounded-xl bg-white/20 hover:bg-white/30 text-white text-xs font-medium transition cursor-pointer text-center block"
                    >
                        Pilih dari Galeri / Dokumen
                    </label>
                </div>
            </div>

            <!-- Live Video Stream -->
            <video 
                x-ref="videoElement" 
                x-show="!capturedDataUrl && !cameraError" 
                playsinline 
                autoplay 
                muted 
                class="w-full h-full object-contain rounded-2xl"
                :class="facingMode === 'user' ? 'scale-x-[-1]' : ''"
            ></video>

            <!-- Hidden Canvas for Snapshotting -->
            <canvas x-ref="canvasElement" class="hidden"></canvas>

            <!-- Captured Photo Preview -->
            <img 
                x-show="capturedDataUrl" 
                :src="capturedDataUrl" 
                class="w-full h-full object-contain rounded-2xl"
                style="display: none;" 
                alt="Foto Hasil Jepretan"
            />
        </div>

        <!-- Camera Controls Bottom Bar -->
        <div class="py-3 flex flex-col items-center justify-center gap-3 z-10">
            <!-- Shutter state: when live video is active -->
            <div x-show="!capturedDataUrl && !cameraError && !cameraLoading" class="flex flex-col items-center gap-3">
                <button 
                    type="button" 
                    @click="takeSnapshot()" 
                    class="size-16 rounded-full border-4 border-white flex items-center justify-center bg-white/30 active:scale-90 transition p-1 shadow-lg"
                    title="Jepret Foto"
                >
                    <span class="size-full rounded-full bg-white block"></span>
                </button>
                <!-- Quick link for native phone camera -->
                <label 
                    for="chat-camera-native" 
                    @click="closeCamera()" 
                    class="text-[11px] text-gray-300 hover:text-white underline cursor-pointer"
                >
                    Atau buka aplikasi kamera bawaan ponsel
                </label>
            </div>

            <!-- Review state: when photo has been taken -->
            <div x-show="capturedDataUrl" class="flex items-center gap-3 w-full max-w-xs" style="display: none;">
                <button 
                    type="button" 
                    @click="retakePhoto()" 
                    class="flex-1 py-2.5 rounded-xl bg-white/20 hover:bg-white/30 text-white text-xs font-semibold transition active:scale-95 flex items-center justify-center gap-1.5"
                >
                    <flux:icon name="arrow-path" class="size-4" />
                    <span>Ulangi Foto</span>
                </button>
                <button 
                    type="button" 
                    @click="usePhoto()" 
                    class="flex-1 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold transition active:scale-95 flex items-center justify-center gap-1.5 shadow-lg shadow-emerald-600/50"
                >
                    <flux:icon name="check" class="size-4" />
                    <span>Gunakan Foto</span>
                </button>
            </div>
        </div>
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
            <span class="text-xs font-medium text-gray-300">Pratinjau Foto</span>
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
