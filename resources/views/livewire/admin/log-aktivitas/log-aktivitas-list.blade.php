<div>
    <x-slot:title>
        Log Aktivitas
    </x-slot:title>

    <flux:main>
        <div class="flex items-center justify-between w-full mb-6">
            <div class="flex flex-col gap-1">
                <flux:heading size="xl" level="1">{{ __('Log Aktivitas') }}</flux:heading>
                <flux:subheading size="lg">{{ __('Merekam semua perubahan data.') }}</flux:subheading>
            </div>
        </div>

        <div class="flex items-center justify-between mb-4">
            <div class="w-full max-w-sm">
                <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Cari model atau user..." />
            </div>
        </div>

        <flux:card>
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Waktu</flux:table.column>
                    <flux:table.column>Pengguna</flux:table.column>
                    <flux:table.column>Aksi</flux:table.column>
                    <flux:table.column>Model & ID</flux:table.column>
                    <flux:table.column>Perubahan Data</flux:table.column>
                    <flux:table.column>Undo</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse($logs as $log)
                        <flux:table.row>
                            <flux:table.cell class="whitespace-nowrap">
                                {{ $log->created_at->format('d/m/Y H:i:s') }}
                            </flux:table.cell>
                            
                            <flux:table.cell>
                                @if($log->user)
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium text-sm">{{ $log->user->name }}</span>
                                    </div>
                                @else
                                    <span class="text-zinc-400 italic">Sistem/Guest</span>
                                @endif
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:badge color="{{ $log->aksi === 'create' ? 'success' : ($log->aksi === 'update' ? 'warning' : 'danger') }}" variant="solid" size="sm">
                                    {{ strtoupper($log->aksi) }}
                                </flux:badge>
                            </flux:table.cell>

                            <flux:table.cell>
                                <div class="text-sm">
                                    @php
                                        $modelName = class_basename($log->model);
                                    @endphp
                                    <span class="font-medium">{{ $modelName }}</span>
                                    <span class="text-zinc-500">#{{ $log->model_id }}</span>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:modal.trigger name="detail-log-{{ $log->id }}">
                                    <flux:button size="xs" variant="subtle" icon="code-bracket">Lihat Data</flux:button>
                                </flux:modal.trigger>

                                <flux:modal name="detail-log-{{ $log->id }}" class="md:w-3/4 max-w-4xl">
                                    <div class="space-y-6">
                                        <flux:heading size="lg">Detail Perubahan Data (JSON)</flux:heading>
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <flux:subheading class="mb-2">Data Lama</flux:subheading>
                                                <div class="bg-zinc-100 dark:bg-zinc-900 p-4 rounded-lg overflow-auto max-h-96 text-xs font-mono">
                                                    @if($log->data_lama)
                                                        <pre>{{ json_encode($log->data_lama, JSON_PRETTY_PRINT) }}</pre>
                                                    @else
                                                        <span class="text-zinc-500 italic">Tidak ada (NULL)</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div>
                                                <flux:subheading class="mb-2">Data Baru</flux:subheading>
                                                <div class="bg-zinc-100 dark:bg-zinc-900 p-4 rounded-lg overflow-auto max-h-96 text-xs font-mono">
                                                    @if($log->data_baru)
                                                        <pre>{{ json_encode($log->data_baru, JSON_PRETTY_PRINT) }}</pre>
                                                    @else
                                                        <span class="text-zinc-500 italic">Tidak ada (NULL)</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex justify-end gap-2">
                                            <flux:modal.close>
                                                <flux:button variant="ghost">Tutup</flux:button>
                                            </flux:modal.close>
                                        </div>
                                    </div>
                                </flux:modal>
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:modal.trigger name="undo-log-{{ $log->id }}">
                                    <flux:button size="xs" variant="danger" icon="arrow-uturn-left" />
                                </flux:modal.trigger>

                                <flux:modal name="undo-log-{{ $log->id }}">
                                    <div class="space-y-6">
                                        <flux:heading size="lg">Konfirmasi Undo</flux:heading>
                                        <flux:text>
                                            Apakah Anda yakin ingin membatalkan (undo) aksi <strong>{{ strtoupper($log->aksi) }}</strong> pada <strong>{{ class_basename($log->model) }} #{{ $log->model_id }}</strong>?<br><br>
                                            Ini akan merestorasi data ke keadaan sebelum aksi ini dilakukan secara paksa.
                                        </flux:text>
                                        
                                        <div class="flex gap-2">
                                            <flux:spacer />
                                            <flux:modal.close>
                                                <flux:button variant="ghost">Batal</flux:button>
                                            </flux:modal.close>
                                            
                                            <flux:button 
                                                wire:click="undo({{ $log->id }})" 
                                                variant="danger" 
                                                x-on:click="$flux.modal('undo-log-{{ $log->id }}').close()"
                                            >
                                                Ya, Lakukan Undo
                                            </flux:button>
                                        </div>
                                    </div>
                                </flux:modal>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="6" class="text-center text-zinc-500 py-8">
                                Belum ada aktivitas yang tercatat.
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>

            <div class="mt-4 p-4">
                {{ $logs->links() }}
            </div>
        </flux:card>
    </flux:main>
</div>
