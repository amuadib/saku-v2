<?php

namespace App\Livewire\Admin\Setting;

use App\Models\Setting;
use Flux\Flux;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Component;

class SettingForm extends Component
{
    public $app_nama;

    public $app_singkatan;

    public $app_keterangan;

    public $lembaga_items = [];

    public function mount()
    {
        $this->app_nama = config('custom.app.nama');
        $this->app_singkatan = config('custom.app.singkatan');
        $this->app_keterangan = config('custom.app.keterangan');

        $this->loadLembagaItems();
    }

    public function loadLembagaItems()
    {
        $lembagaItems = config('custom.lembaga') ?? [];
        foreach ($lembagaItems as &$lembaga) {
            $lembaga['tingkat_string'] = implode(', ', $lembaga['tingkat'] ?? []);
            $jamKerja = $lembaga['jam_kerja'] ?? [];
            $lembaga['jam_kerja_string'] = is_array($jamKerja) ? implode("\n", $jamKerja) : (string) $jamKerja;
        }
        $this->lembaga_items = $lembagaItems;
    }

    public function syncLembaga()
    {
        $baseUrl = config('custom.api_akademik.url');
        $token = config('custom.api_akademik.token');

        if (empty($baseUrl)) {
            Flux::toast('URL API Akademik belum dikonfigurasi di file .env', variant: 'danger');

            return;
        }

        $apiUrl = rtrim($baseUrl, '/').'/api/lembaga/sync';

        try {
            $request = Http::withToken($token)->timeout(30);
            if (app()->environment('local')) {
                $request->withoutVerifying();
            }

            $response = $request->get($apiUrl);

            if (! $response->successful()) {
                Flux::toast('Gagal menghubungi API Master: HTTP '.$response->status(), variant: 'danger');

                return;
            }

            $raw = $response->json();
            $data = $raw['data'] ?? $raw;
            $items = $data['lembaga'] ?? $data;

            if (empty($items) || ! is_array($items)) {
                Flux::toast('Data lembaga dari API Master kosong atau format tidak sesuai', variant: 'danger');

                return;
            }

            $currentLembaga = config('custom.lembaga') ?? [];
            $syncedLembaga = [];

            foreach ($items as $key => $item) {
                if (! is_array($item)) {
                    continue;
                }

                $id = (int) ($item['id'] ?? $key);
                if (! $id && $key !== 0 && $key !== '0') {
                    continue;
                }

                // Normalisasi jam_kerja
                $jamKerja = $item['jam_kerja'] ?? ($currentLembaga[$id]['jam_kerja'] ?? []);
                if (is_string($jamKerja)) {
                    $jamKerja = array_values(array_filter(array_map('trim', explode("\n", $jamKerja))));
                } elseif (is_array($jamKerja)) {
                    $jamKerja = array_values($jamKerja);
                } else {
                    $jamKerja = [];
                }

                // Normalisasi tingkat
                $tingkat = $item['tingkat'] ?? ($currentLembaga[$id]['tingkat'] ?? []);
                if (is_string($tingkat)) {
                    $tingkat = array_values(array_filter(array_map('trim', explode(',', $tingkat))));
                } elseif (is_array($tingkat)) {
                    $tingkat = array_values($tingkat);
                } else {
                    $tingkat = [];
                }

                $singkatan = $item['singkatan'] ?? ($item['alias'] ?? ($currentLembaga[$id]['singkatan'] ?? ''));
                $alias = $item['alias'] ?? ($item['singkatan'] ?? ($currentLembaga[$id]['alias'] ?? $singkatan));

                $logo = $item['logo_url'] ?? ($item['logo'] ?? ($currentLembaga[$id]['logo'] ?? null));
                $kop = $item['kop_url'] ?? ($item['kop'] ?? ($currentLembaga[$id]['kop'] ?? null));

                $syncedLembaga[$id] = [
                    'nama' => $item['nama'] ?? ($currentLembaga[$id]['nama'] ?? ''),
                    'singkatan' => $singkatan,
                    'alias' => $alias,
                    'alamat' => $item['alamat'] ?? ($currentLembaga[$id]['alamat'] ?? ''),
                    'kontak' => $item['kontak'] ?? ($currentLembaga[$id]['kontak'] ?? ''),
                    'telp' => $item['telp'] ?? ($currentLembaga[$id]['telp'] ?? ''),
                    'lat' => $item['lat'] ?? ($currentLembaga[$id]['lat'] ?? 0),
                    'lon' => $item['lon'] ?? ($currentLembaga[$id]['lon'] ?? 0),
                    'logo' => $logo,
                    'kop' => $kop,
                    'logo_url' => $item['logo_url'] ?? $logo,
                    'kop_url' => $item['kop_url'] ?? $kop,
                    'tingkat' => $tingkat,
                    'jam_kerja' => $jamKerja,
                ];
            }

            if (empty($syncedLembaga)) {
                Flux::toast('Tidak ada item lembaga valid yang dapat disinkronkan', variant: 'warning');

                return;
            }

            Setting::updateOrCreate(
                ['key' => 'lembaga'],
                ['payload' => $syncedLembaga]
            );

            Cache::forget('custom_settings');

            // Perbarui config custom lembaga di runtime
            $customConfig = config('custom', []);
            $customConfig['lembaga'] = $syncedLembaga;
            config(['custom' => $customConfig]);

            $this->loadLembagaItems();

            Flux::toast('Berhasil menyinkronkan data lembaga dari API Master');
        } catch (\Exception $e) {
            Log::error('Sinkronisasi lembaga gagal: '.$e->getMessage());
            Flux::toast('Terjadi kesalahan saat sinkronisasi: '.$e->getMessage(), variant: 'danger');
        }
    }

    public function save()
    {
        $this->validate([
            'app_nama' => 'required|string',
            'app_singkatan' => 'required|string',
            'app_keterangan' => 'nullable|string',
        ]);

        $appConfig = [
            'nama' => $this->app_nama,
            'singkatan' => $this->app_singkatan,
            'keterangan' => $this->app_keterangan,
        ];

        Setting::updateOrCreate(
            ['key' => 'app'],
            ['payload' => $appConfig]
        );

        Cache::forget('custom_settings');

        // Perbarui config custom app di runtime
        $customConfig = config('custom', []);
        $customConfig['app'] = $appConfig;
        config(['custom' => $customConfig]);

        Flux::toast('Pengaturan aplikasi berhasil disimpan');
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.admin.setting.setting-form');
    }
}
