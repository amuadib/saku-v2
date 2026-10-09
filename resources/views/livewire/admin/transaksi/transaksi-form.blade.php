<div>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Tambah Transaksi Manual
            </h2>
            <flux:button variant="outline" href="{{ route('admin.transaksi.index') }}" wire:navigate>Kembali</flux:button>
        </div>
    </x-slot>

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mb-6 max-w-2xl mx-auto mt-6">
        <div class="p-6">
            <form wire:submit="save">
                <div class="space-y-6">
                    <flux:field>
                            <flux:label>Jenis Transaksi</flux:label>
                            <flux:radio.group wire:model.live="jenis">
                                    <flux:radio value="tun" label="Tunai" />
                                    <flux:radio value="trf" label="Transfer Antar Kas" />
                            </flux:radio.group>
                        </flux:field>

                    @if($jenis == 'tun')
                    <flux:field>
                            <flux:label>Mutasi</flux:label>
                            <flux:radio.group wire:model.live="mutasi">
                                    <flux:radio value="masuk" label="Masuk" />
                                    <flux:radio value="keluar" label="Keluar" />
                            </flux:radio.group>
                    </flux:field>
                    @endif

                    @if(auth()->check() && auth()->user()->isAdmin())
                        <flux:field>
                            <flux:label>Lembaga</flux:label>
                            <flux:radio.group wire:model.live="lembaga_id">
                                @foreach($lembagas as $id => $nama)
                                    <flux:radio value="{{ $id }}" label="{{ $nama }}" />
                                @endforeach
                            </flux:radio.group>
                        </flux:field>
                    @endif

                    @if($jenis == 'tun')
                    <flux:field>
                        <flux:label>Kas</flux:label>
                        <flux:select wire:model="kas_id" placeholder="Pilih Kas">
                            @foreach($kas_items as $k)
                                <flux:select.option value="{{ $k->id }}">{{ $k->nama }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="kas_id" />
                    </flux:field>
                    @else
                    <flux:field>
                        <flux:label>Kas Asal</flux:label>
                        <flux:select wire:model="kas_id_asal" placeholder="Pilih Kas">
                            @foreach($kas_items as $k)
                                <flux:select.option value="{{ $k->id }}">{{ $k->nama }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="kas_id_asal" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Kas Tujuan</flux:label>
                        <flux:select wire:model="kas_id_tujuan" placeholder="Pilih Kas">
                            @foreach($kas_items as $k)
                                <flux:select.option value="{{ $k->id }}">{{ $k->nama }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="kas_id_tujuan" />
                    </flux:field>
                    @endif

                    <flux:field>
                        <flux:label>Jumlah (Nominal)</flux:label>
                        <div x-data="{
                            raw: @entangle('jumlah'),
                            formatted: '',
                            init() {
                                this.formatted = this.raw ? this.raw.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '';
                                this.$watch('raw', value => {
                                    this.formatted = value ? value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '';
                                });
                            },
                            formatInput() {
                                let clean = this.formatted.toString().replace(/\D/g, '');
                                this.raw = clean;
                                this.formatted = clean.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                            }
                        }">
                            <flux:input type="text" x-model="formatted" @input="formatInput" placeholder="Masukkan jumlah transaksi" />
                        </div>
                        <flux:error name="jumlah" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Keterangan</flux:label>
                        <flux:textarea wire:model="keterangan" rows="3" placeholder="Deskripsi transaksi manual..." />
                        <flux:error name="keterangan" />
                    </flux:field>

                    <div class="flex justify-end pt-4">
                        <flux:button type="submit" variant="primary">Simpan Transaksi</flux:button>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>
