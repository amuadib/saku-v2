<div>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                @if($siswa->foto)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($siswa->foto) }}" class="size-12 object-cover rounded-full border border-zinc-200 dark:border-zinc-700 shadow-sm" alt="{{ $siswa->nama }}">
                @else
                    <div class="size-12 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center font-bold text-zinc-600 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700">
                        {{ strtoupper(substr($siswa->nama, 0, 2)) }}
                    </div>
                @endif
                <div>
                    <h2 class="font-semibold text-xl text-zinc-900 dark:text-zinc-100 leading-tight flex items-center gap-2">
                        <span>Aktivitas & Log Akses: {{ $siswa->nama }}</span>
                        @if($siswa->status == 1)
                            <flux:badge color="green" size="sm">Aktif</flux:badge>
                        @else
                            <flux:badge color="red" size="sm">Nonaktif</flux:badge>
                        @endif
                    </h2>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                        NIS: <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $siswa->nis ?? '-' }}</span>
                        • NISN: <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $siswa->nisn ?? '-' }}</span>
                        • Kelas: <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ optional($siswa->kelas)->nama ?? '-' }}</span>
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <flux:button variant="outline" size="sm" href="{{ route('admin.siswa.show', $siswa->id) }}" wire:navigate icon="user">
                    Detail Siswa
                </flux:button>
                <flux:button variant="outline" size="sm" href="{{ route('admin.siswa.index') }}" wire:navigate icon="arrow-left">
                    Kembali
                </flux:button>
            </div>
        </div>
    </x-slot>

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Last Login Card -->
        <flux:card class="space-y-2">
            <div class="flex items-center justify-between text-zinc-500 dark:text-zinc-400">
                <span class="text-xs font-medium uppercase tracking-wider">Login Terakhir</span>
                <flux:icon name="arrow-right-end-on-rectangle" class="size-4 text-emerald-500" />
            </div>
            @if($stats['last_login'])
                <div>
                    <div class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
                        {{ $stats['last_login']->translatedFormat('d M Y, H:i') }}
                    </div>
                    <div class="text-xs text-emerald-600 dark:text-emerald-400 mt-0.5">
                        {{ $stats['last_login']->diffForHumans() }}
                    </div>
                </div>
            @else
                <div>
                    <div class="text-sm font-medium text-zinc-400 dark:text-zinc-500 italic">
                        Belum pernah login
                    </div>
                    <div class="text-xs text-zinc-400 mt-0.5">
                        Siswa belum pernah masuk
                    </div>
                </div>
            @endif
        </flux:card>

        <!-- Last IP Card -->
        <flux:card class="space-y-2">
            <div class="flex items-center justify-between text-zinc-500 dark:text-zinc-400">
                <span class="text-xs font-medium uppercase tracking-wider">IP Terakhir</span>
                <flux:icon name="globe-alt" class="size-4 text-blue-500" />
            </div>
            <div>
                <div class="text-base font-semibold font-mono text-zinc-900 dark:text-zinc-100">
                    {{ $stats['last_ip'] ?? '-' }}
                </div>
                <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5 truncate">
                    @if($stats['last_activity'])
                        {{ $stats['last_activity']->device ?? 'Unknown' }} • {{ $stats['last_activity']->browser ?? 'Browser' }}
                    @else
                        Belum ada data IP
                    @endif
                </div>
            </div>
        </flux:card>

        <!-- Total Login Card -->
        <flux:card class="space-y-2">
            <div class="flex items-center justify-between text-zinc-500 dark:text-zinc-400">
                <span class="text-xs font-medium uppercase tracking-wider">Total Login</span>
                <flux:icon name="shield-check" class="size-4 text-purple-500" />
            </div>
            <div>
                <div class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
                    {{ number_format($stats['total_login']) }} kali
                </div>
                <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                    Total sesi login tercatat
                </div>
            </div>
        </flux:card>

        <!-- Total Link Access Card -->
        <flux:card class="space-y-2">
            <div class="flex items-center justify-between text-zinc-500 dark:text-zinc-400">
                <span class="text-xs font-medium uppercase tracking-wider">Akses & Perubahan</span>
                <flux:icon name="cursor-arrow-rays" class="size-4 text-amber-500" />
            </div>
            <div>
                <div class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
                    {{ number_format($stats['total_akses']) }} akses
                </div>
                <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                    {{ number_format($stats['total_ubah_data']) }} kali ubah profil / data
                </div>
            </div>
        </flux:card>
    </div>

    <!-- Filter & Content Card -->
    <flux:card class="space-y-4">
        <!-- Search and Filters -->
        <div class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-center justify-between pb-3 border-b border-zinc-200 dark:border-zinc-700">
            <div class="flex flex-wrap items-center gap-2 flex-1">
                <div class="w-full sm:w-72">
                    <flux:input
                        wire:model.live.debounce.300ms="search"
                        icon="magnifying-glass"
                        placeholder="Cari URL, IP, keterangan..."
                        size="sm"
                        clearable
                    />
                </div>
                <div class="w-40">
                    <flux:select wire:model.live="filter_tipe" size="sm">
                        <option value="">Semua Tipe</option>
                        <option value="login">Login Saja</option>
                        <option value="akses_link">Akses Link Saja</option>
                        <option value="ubah_data">Ubah Data Saja</option>
                    </flux:select>
                </div>
                <div class="w-36">
                    <flux:select wire:model.live="sort_order" size="sm">
                        <option value="desc">Waktu Terbaru</option>
                        <option value="asc">Waktu Terlama</option>
                    </flux:select>
                </div>
                @if($search || $filter_tipe || $sort_order !== 'desc')
                    <flux:button variant="ghost" size="sm" wire:click="resetFilters" icon="x-mark">
                        Reset
                    </flux:button>
                @endif
            </div>
            <div class="text-xs text-zinc-500 dark:text-zinc-400 self-end sm:self-center">
                Total: <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $aktivitas->total() }}</span> aktivitas
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column class="w-48">Waktu</flux:table.column>
                    <flux:table.column class="w-28">Tipe</flux:table.column>
                    <flux:table.column>Aktivitas / Keterangan</flux:table.column>
                    <flux:table.column>Link / URL yang Diakses</flux:table.column>
                    <flux:table.column class="w-36">IP Address</flux:table.column>
                    <flux:table.column class="w-44">Perangkat / Browser</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse($aktivitas as $item)
                        <flux:table.row wire:key="act-{{ $item->id }}">
                            <!-- Waktu -->
                            <flux:table.cell>
                                <div class="text-xs font-semibold text-zinc-900 dark:text-zinc-100">
                                    {{ $item->created_at->translatedFormat('d M Y, H:i:s') }}
                                </div>
                                <div class="text-[11px] text-zinc-500 dark:text-zinc-400">
                                    {{ $item->created_at->diffForHumans() }}
                                </div>
                            </flux:table.cell>

                            <!-- Tipe -->
                            <flux:table.cell>
                                @if($item->tipe === 'login')
                                    <flux:badge color="green" size="sm" icon="arrow-right-end-on-rectangle">Login</flux:badge>
                                @elseif($item->tipe === 'akses_link')
                                    <flux:badge color="blue" size="sm" icon="link">Akses Link</flux:badge>
                                @elseif($item->tipe === 'ubah_data')
                                    <flux:badge color="amber" size="sm" icon="pencil-square">Ubah Data</flux:badge>
                                @else
                                    <flux:badge color="zinc" size="sm">{{ ucfirst($item->tipe) }}</flux:badge>
                                @endif
                            </flux:table.cell>

                            <!-- Keterangan -->
                            <flux:table.cell>
                                <div class="text-sm font-medium text-zinc-800 dark:text-zinc-200">
                                    {{ $item->keterangan ?: '-' }}
                                </div>
                                @if($item->route_name && !str_contains($item->route_name, 'livewire'))
                                    <div class="text-[11px] text-zinc-400 font-mono">
                                        route: {{ $item->route_name }}
                                    </div>
                                @endif

                                @if($item->tipe === 'ubah_data' && is_array($item->data_baru) && count($item->data_baru) > 0)
                                    @php
                                        $fieldLabels = \App\Models\SiswaAktivitas::getFieldLabels();
                                    @endphp
                                    <div class="mt-2 space-y-1.5 bg-zinc-50 dark:bg-zinc-800/80 p-2.5 rounded-lg border border-zinc-200 dark:border-zinc-700/70 text-xs">
                                        <div class="text-[11px] font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider flex items-center gap-1">
                                            <flux:icon name="arrow-path" class="size-3 text-amber-500" />
                                            <span>Perubahan (Lama &gt;&gt; Baru):</span>
                                        </div>
                                        @foreach($item->data_baru as $field => $newVal)
                                            @php
                                                $oldVal = $item->data_lama[$field] ?? null;
                                                $label = $fieldLabels[$field] ?? ucfirst(str_replace('_', ' ', $field));
                                            @endphp
                                            <div class="flex items-center gap-1.5 flex-wrap text-xs pt-1 border-t border-zinc-200/60 dark:border-zinc-700/50 first:border-0 first:pt-0">
                                                <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $label }}:</span>
                                                <span class="line-through text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/40 px-1.5 py-0.5 rounded font-mono text-[11px]">
                                                    {{ $oldVal !== null && $oldVal !== '' ? $oldVal : '(kosong)' }}
                                                </span>
                                                <span class="text-zinc-400 font-bold text-xs">&gt;&gt;</span>
                                                <span class="font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-1.5 py-0.5 rounded font-mono text-[11px]">
                                                    {{ $newVal !== null && $newVal !== '' ? $newVal : '(kosong)' }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </flux:table.cell>

                            <!-- Link / URL -->
                            <flux:table.cell>
                                @php
                                    $displayUrl = $item->url;
                                    if ($displayUrl && str_contains($displayUrl, 'livewire')) {
                                        $displayUrl = route('siswa.profil.detail');
                                    }
                                @endphp
                                @if($displayUrl)
                                    <div class="flex items-center gap-1.5 max-w-md">
                                        <flux:badge size="sm" color="{{ $item->method === 'POST' ? 'amber' : 'zinc' }}" class="text-[10px] font-mono px-1 py-0">
                                            {{ $item->method ?: 'GET' }}
                                        </flux:badge>
                                        <a href="{{ $displayUrl }}" target="_blank" class="text-xs text-blue-600 dark:text-blue-400 hover:underline truncate font-mono flex items-center gap-1" title="{{ $displayUrl }}">
                                            <span>{{ parse_url($displayUrl, PHP_URL_PATH) ?: $displayUrl }}</span>
                                            <flux:icon name="arrow-top-right-on-square" class="size-3 shrink-0" />
                                        </a>
                                    </div>
                                    @if(parse_url($displayUrl, PHP_URL_QUERY))
                                        <div class="text-[10px] text-zinc-400 font-mono truncate max-w-md" title="{{ parse_url($displayUrl, PHP_URL_QUERY) }}">
                                            ?{{ parse_url($displayUrl, PHP_URL_QUERY) }}
                                        </div>
                                    @endif
                                @else
                                    <span class="text-xs text-zinc-400">-</span>
                                @endif
                            </flux:table.cell>

                            <!-- IP Address -->
                            <flux:table.cell>
                                @if($item->ip_address)
                                    <span class="font-mono text-xs text-zinc-700 dark:text-zinc-300 bg-zinc-100 dark:bg-zinc-800 px-2 py-0.5 rounded border border-zinc-200 dark:border-zinc-700">
                                        {{ $item->ip_address }}
                                    </span>
                                @else
                                    <span class="text-xs text-zinc-400">-</span>
                                @endif
                            </flux:table.cell>

                            <!-- Perangkat / Browser -->
                            <flux:table.cell>
                                <div class="text-xs text-zinc-800 dark:text-zinc-200 font-medium">
                                    {{ $item->device ?: 'Unknown' }}
                                </div>
                                <div class="text-[11px] text-zinc-500 dark:text-zinc-400 truncate" title="{{ $item->user_agent }}">
                                    {{ $item->browser ?: '-' }}
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="6" class="text-center py-8">
                                <div class="flex flex-col items-center justify-center text-zinc-400 dark:text-zinc-500">
                                    <flux:icon name="clock" class="size-10 stroke-1 mb-2 text-zinc-300 dark:text-zinc-600" />
                                    <p class="text-sm font-medium">Belum ada riwayat aktivitas tercatat.</p>
                                    <p class="text-xs mt-1 text-zinc-400">Aktivitas login dan akses tautan siswa akan otomatis tercatat di sini.</p>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>

        @if($aktivitas->hasPages())
            <div class="pt-3 border-t border-zinc-200 dark:border-zinc-700">
                {{ $aktivitas->links() }}
            </div>
        @endif
    </flux:card>
</div>
