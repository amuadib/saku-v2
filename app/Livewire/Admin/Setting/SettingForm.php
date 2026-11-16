<?php

namespace App\Livewire\Admin\Setting;

use App\Models\Setting;
use Flux\Flux;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

class SettingForm extends Component
{
    use WithFileUploads;

    public $app_nama;

    public $app_singkatan;

    public $app_keterangan;

    public $lembaga_items = [];

    public bool $showImportModal = false;

    public $json_file = null;

    public string $json_text = '';

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
        foreach ($lembagaItems as $id => &$lembaga) {
            $lembaga['tingkat_string'] = implode(', ', $lembaga['tingkat'] ?? []);
            $jamKerja = $lembaga['jam_kerja'] ?? [];
            $lembaga['jam_kerja_string'] = is_array($jamKerja) ? implode("\n", $jamKerja) : (string) $jamKerja;
            $lembaga['kepala'] = $lembaga['kepala'] ?? '';
            $lembaga['nip_kepala'] = $lembaga['nip_kepala'] ?? '';
            $lembaga['email'] = $lembaga['email'] ?? '';
            $lembaga['lat'] = $lembaga['lat'] ?? '';
            $lembaga['lon'] = $lembaga['lon'] ?? '';
            $lembaga['maps_embed'] = $lembaga['maps_embed'] ?? '';
            $lembaga['maps_embed_url'] = $this->getMapsEmbedUrl($lembaga);
        }
        $this->lembaga_items = $lembagaItems;
    }

    public function getMapsEmbedUrl(array|int|string $lembagaOrId): ?string
    {
        $lembaga = is_array($lembagaOrId) ? $lembagaOrId : ($this->lembaga_items[$lembagaOrId] ?? (config('custom.lembaga.'.$lembagaOrId) ?? null));
        if (! $lembaga) {
            return null;
        }

        if (! empty($lembaga['maps_embed_url'])) {
            return $lembaga['maps_embed_url'];
        }

        $embed = trim((string) ($lembaga['maps_embed'] ?? ''));
        if (! empty($embed)) {
            if (preg_match('/src=["\']([^"\']+)["\']/', $embed, $matches)) {
                return $matches[1];
            }
            if (str_starts_with($embed, 'http')) {
                return $embed;
            }
        }

        $lat = trim((string) ($lembaga['lat'] ?? ''));
        $lon = trim((string) ($lembaga['lon'] ?? ''));
        if (! empty($lat) && ! empty($lon) && (float) $lat != 0 && (float) $lon != 0) {
            return "https://maps.google.com/maps?q={$lat},{$lon}&hl=id&z=16&output=embed";
        }

        if (! empty($lembaga['alamat'])) {
            return 'https://maps.google.com/maps?q='.urlencode($lembaga['alamat']).'&hl=id&z=15&output=embed';
        }

        return null;
    }

    public function resetImportModal(): void
    {
        $this->reset(['json_file', 'json_text']);
        $this->resetErrorBag();
    }

    public function importJson(?string $directJson = null): void
    {
        if ($directJson !== null && trim($directJson) !== '') {
            $this->json_text = $directJson;
            $this->processImportJson();

            return;
        }

        $this->resetImportModal();
        $this->showImportModal = true;
        Flux::modal('import-json-modal')->show();
    }

    public function processImportJson(): void
    {
        $this->resetErrorBag();

        $jsonContent = null;

        if (! empty(trim($this->json_text))) {
            $jsonContent = trim($this->json_text);
        } elseif ($this->json_file) {
            $this->validate([
                'json_file' => 'required|file|max:5120',
            ], [
                'json_file.required' => 'Pilih file JSON untuk diimpor.',
                'json_file.file' => 'File yang diunggah tidak valid.',
                'json_file.max' => 'Ukuran file JSON maksimal 5MB.',
            ]);

            $jsonContent = method_exists($this->json_file, 'get')
                ? $this->json_file->get()
                : file_get_contents($this->json_file->getRealPath());
        } else {
            $this->addError('import_error', 'Silakan pilih file JSON atau tempel teks JSON terlebih dahulu.');

            return;
        }

        if (empty($jsonContent)) {
            $this->addError('import_error', 'Konten JSON kosong.');

            return;
        }

        // Hapus trailing comma sebelum ']' atau '}' jika ada
        $sanitized = preg_replace('/,\s*([\]}])/m', '$1', $jsonContent);

        $decoded = json_decode($sanitized, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
            $this->addError('import_error', 'Format JSON tidak valid: '.json_last_error_msg());

            return;
        }

        $success = $this->applyLembagaData($decoded, 'File/Teks JSON');

        if ($success) {
            $this->showImportModal = false;
            Flux::modal('import-json-modal')->close();
            $this->reset(['json_file', 'json_text']);
        }
    }

    public function applyLembagaData(array $raw, string $source = 'API Master'): bool
    {
        $data = $raw['data'] ?? $raw;
        $items = $data['lembaga'] ?? $data;

        // Jika format JSON langsung berupa 1 objek lembaga { "nama": "...", ... }
        if (is_array($items) && isset($items['nama']) && ! isset($items[1])) {
            $items = [1 => $items];
        }

        if (empty($items) || ! is_array($items)) {
            $msg = "Data lembaga dari {$source} kosong atau format tidak sesuai";
            Flux::toast($msg, variant: 'danger');
            $this->addError('import_error', $msg);

            return false;
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
            $ttd = $item['ttd_url'] ?? ($item['ttd'] ?? ($currentLembaga[$id]['ttd'] ?? null));
            $stempel = $item['stempel_url'] ?? ($item['stempel'] ?? ($currentLembaga[$id]['stempel'] ?? null));

            $email = $item['email'] ?? ($currentLembaga[$id]['email'] ?? '');
            $lat = $item['lat'] ?? ($currentLembaga[$id]['lat'] ?? '');
            $lon = $item['lon'] ?? ($currentLembaga[$id]['lon'] ?? '');
            $mapsEmbed = $item['maps_embed'] ?? ($currentLembaga[$id]['maps_embed'] ?? '');
            $alamat = $item['alamat'] ?? ($currentLembaga[$id]['alamat'] ?? '');
            $mapsEmbedUrl = $item['maps_embed_url'] ?? $this->getMapsEmbedUrl([
                'maps_embed' => $mapsEmbed,
                'lat' => $lat,
                'lon' => $lon,
                'alamat' => $alamat,
            ]);

            $syncedLembaga[$id] = [
                'nama' => $item['nama'] ?? ($currentLembaga[$id]['nama'] ?? ''),
                'singkatan' => $singkatan,
                'alias' => $alias,
                'alamat' => $alamat,
                'kontak' => $item['kontak'] ?? ($currentLembaga[$id]['kontak'] ?? ''),
                'kepala' => $item['kepala'] ?? ($currentLembaga[$id]['kepala'] ?? ''),
                'nip_kepala' => $item['nip_kepala'] ?? ($currentLembaga[$id]['nip_kepala'] ?? ''),
                'telp' => $item['telp'] ?? ($currentLembaga[$id]['telp'] ?? ''),
                'email' => $email,
                'lat' => $lat,
                'lon' => $lon,
                'maps_embed' => $mapsEmbed,
                'maps_embed_url' => $mapsEmbedUrl,
                'logo' => $logo,
                'kop' => $kop,
                'ttd' => $ttd,
                'stempel' => $stempel,
                'logo_url' => $item['logo_url'] ?? $logo,
                'kop_url' => $item['kop_url'] ?? $kop,
                'ttd_url' => $item['ttd_url'] ?? $ttd,
                'stempel_url' => $item['stempel_url'] ?? $stempel,
                'tingkat' => $tingkat,
                'jam_kerja' => $jamKerja,
                'npsn' => $item['npsn'] ?? ($currentLembaga[$id]['npsn'] ?? ''),
                'akreditasi' => $item['akreditasi'] ?? ($currentLembaga[$id]['akreditasi'] ?? ''),
                'website' => $item['website'] ?? ($currentLembaga[$id]['website'] ?? ''),
                'nomor_statistik' => $item['nomor_statistik'] ?? ($currentLembaga[$id]['nomor_statistik'] ?? ''),
                'jalan' => $item['jalan'] ?? ($currentLembaga[$id]['jalan'] ?? ''),
                'desa' => $item['desa'] ?? ($currentLembaga[$id]['desa'] ?? ''),
                'kecamatan' => $item['kecamatan'] ?? ($currentLembaga[$id]['kecamatan'] ?? ''),
                'kota' => $item['kota'] ?? ($currentLembaga[$id]['kota'] ?? ''),
                'propinsi' => $item['propinsi'] ?? ($currentLembaga[$id]['propinsi'] ?? ''),
            ];
        }

        if (empty($syncedLembaga)) {
            $msg = "Tidak ada item lembaga valid yang dapat disinkronkan dari {$source}";
            Flux::toast($msg, variant: 'warning');
            $this->addError('import_error', $msg);

            return false;
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

        Flux::toast("Berhasil menyinkronkan data lembaga dari {$source}");

        return true;
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
            $request = Http::withToken($token)
                ->timeout(30)
                ->connectTimeout(15)
                ->withOptions([
                    'force_ip_resolve' => 'v4',
                ])
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36 SakuApp/2.0',
                    'Accept' => 'application/json',
                ]);

            if (app()->environment('local')) {
                $request->withoutVerifying();
            }

            $response = $request->get($apiUrl);

            if (! $response->successful()) {
                Flux::toast('Gagal menghubungi API Master: HTTP '.$response->status(), variant: 'danger');

                return;
            }

            $raw = $response->json();
            $this->applyLembagaData($raw, 'API Master');
        } catch (\Exception $e) {
            $host = parse_url($apiUrl, PHP_URL_HOST);
            $dns = @gethostbynamel($host);
            $dnsStr = $dns ? implode(', ', $dns) : 'Gagal resolve DNS';
            Log::error("Sinkronisasi lembaga gagal [Host: {$host}, IP: {$dnsStr}]: ".$e->getMessage());
            Flux::toast('Terjadi kesalahan saat sinkronisasi: '.$e->getMessage()." [Resolved IP: {$dnsStr}]", variant: 'danger');
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
