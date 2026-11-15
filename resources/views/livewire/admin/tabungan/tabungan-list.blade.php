<div>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-zinc-800 dark:text-zinc-100 leading-tight">
                Data Tabungan
            </h2>
            <flux:button variant="primary" href="{{ route('admin.tabungan.create') }}" wire:navigate>Tambah Tabungan</flux:button>
        </div>
    </x-slot>

    <div class="bg-white dark:bg-zinc-900/70 shadow-xs sm:rounded-2xl border border-zinc-200 dark:border-zinc-800 mb-6 backdrop-blur-xl">
        <div class="p-4 sm:p-6">
            <div class="mb-4 flex gap-4">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari nama atau NIS siswa..." icon="magnifying-glass" class="w-full sm:w-1/3" />
            </div>

            @if (session()->has('message'))
                <flux:callout variant="success" class="mb-4">
                    {{ session('message') }}
                </flux:callout>
            @endif

            <!-- Dual View: Table vs Mobile Cards -->
            <x-responsive-view>
                <x-slot name="desktop">
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>Siswa</flux:table.column>
                            <flux:table.column>Kas Tabungan</flux:table.column>
                            <flux:table.column>Saldo (Rp)</flux:table.column>
                            <flux:table.column align="end">Aksi</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @forelse ($tabungans as $tabungan)
                                <flux:table.row>
                                    <flux:table.cell>
                                        <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ optional($tabungan->siswa)->nama ?? '-' }}</div>
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400">NIS: {{ optional($tabungan->siswa)->nis ?? '-' }}</div>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <span class="text-zinc-700 dark:text-zinc-300">{{ optional($tabungan->kas)->nama ?? '-' }}</span>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <span class="font-semibold text-emerald-600 dark:text-emerald-400">
                                            Rp {{ number_format($tabungan->saldo, 0, ',', '.') }}
                                        </span>
                                    </flux:table.cell>
                                    <flux:table.cell align="end">
                                        <flux:button size="sm" href="{{ route('admin.tabungan.riwayat', $tabungan->id) }}" wire:navigate icon="receipt-refund" color="zinc" title="Riwayat Tabungan">
                                            Riwayat
                                        </flux:button>
                                    </flux:table.cell>
                                </flux:table.row>
                            @empty
                                <flux:table.row>
                                    <flux:table.cell colspan="4" class="text-center py-8 text-zinc-500 dark:text-zinc-400">Data tidak ditemukan.</flux:table.cell>
                                </flux:table.row>
                            @endforelse
                        </flux:table.rows>
                    </flux:table>
                </x-slot>

                <x-slot name="mobile">
                    @forelse ($tabungans as $tabungan)
                        <x-mobile-card
                            :title="optional($tabungan->siswa)->nama ?? '-'"
                            :subtitle="'NIS: ' . (optional($tabungan->siswa)->nis ?? '-') . ' • ' . (optional($tabungan->kas)->nama ?? '-')"
                            avatar-color="emerald"
                        >
                            <div class="flex items-center justify-between pt-1 border-t border-zinc-100 dark:border-zinc-700/80">
                                <span class="text-xs text-zinc-500 dark:text-zinc-400">Saldo Tabungan</span>
                                <span class="font-bold text-emerald-600 dark:text-emerald-400 text-sm whitespace-nowrap">
                                    Rp {{ number_format($tabungan->saldo, 0, ',', '.') }}
                                </span>
                            </div>

                            <x-slot name="actions">
                                <flux:button size="sm" href="{{ route('admin.tabungan.riwayat', $tabungan->id) }}" wire:navigate icon="receipt-refund" color="zinc" class="w-full justify-center">
                                    Lihat Riwayat
                                </flux:button>
                            </x-slot>
                        </x-mobile-card>
                    @empty
                        <div class="text-center py-8 text-zinc-500 dark:text-zinc-400 text-sm">
                            Data tidak ditemukan.
                        </div>
                    @endforelse
                </x-slot>
            </x-responsive-view>

            <div class="mt-4">
                {{ $tabungans->links() }}
            </div>
        </div>
    </div>
</div>
