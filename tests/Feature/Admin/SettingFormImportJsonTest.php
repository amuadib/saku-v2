<?php

use App\Livewire\Admin\Setting\SettingForm;
use App\Models\Anggota;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->adminAnggota = Anggota::create([
        'nama' => 'Admin Setting',
        'lembaga_id' => 1,
        'jenis_kelamin' => 'l',
    ]);

    $this->admin = User::factory()->create([
        'role_id' => 1,
        'authable_type' => Anggota::class,
        'authable_id' => $this->adminAnggota->id,
    ]);
});

it('dapat membuka modal impor json', function () {
    $this->actingAs($this->admin);

    Livewire::test(SettingForm::class)
        ->assertSet('showImportModal', false)
        ->call('importJson')
        ->assertSet('showImportModal', true)
        ->assertSet('json_text', '')
        ->assertSet('json_file', null);
});

it('dapat me-reset modal impor json', function () {
    $this->actingAs($this->admin);

    Livewire::test(SettingForm::class)
        ->set('json_text', 'some test')
        ->call('resetImportModal')
        ->assertSet('json_text', '')
        ->assertSet('json_file', null);
});

it('dapat mengimpor data lembaga dari teks json dengan format user termasuk trailing comma', function () {
    $this->actingAs($this->admin);

    $jsonSampleWithTrailingComma = <<<'JSON'
{
    "status": "success",
    "data": {
        "lembaga": {
            "1": {
                "nama": "SDI Al rafa",
                "singkatan": "SDI",
                "alamat": "Jl. Sudan",
                "kontak": "B. Rara (08000)",
                "jam_kerja": [
                    "Senin - Sabtu: 07:00 - 14:10",
                    "Jum'at: 07:00 - 10:00",
                    "Hari Ahad & Libur Nasional libur"
                ],
                "logo": "/storage/lembaga/logo/test.png",
                "kop": "/storage/lembaga/kop/test.jpg",
                "telp": "089000",
                "tingkat": ["1", "2", "3", "4", "5", "6"],
                "email": "sdi.alrafa@gmail.com",
                "lat": "",
                "lon": "",
                "maps_embed": "<iframe src=\"https://www.google.com/maps/embed?pb=xxx\"></iframe>",
                "kepala": "Nanda Augi, S. Pd. I.",
                "ttd": "/storage/lembaga/ttd/test.png",
                "stempel": "/storage/lembaga/stempel/test.png",
                "npsn": "69981912",
                "akreditasi": "B",
                "website": "https://my-website.sch",
                "nomor_statistik": "",
                "jalan": "Jl. rambu RT 02 RW 06 Lingk. Jaranan",
                "desa": "KKletek",
                "kecamatan": "Wiangung",
                "kota": "Blora",
                "propinsi": "Kalimantan Timur",
                "logo_url": "https://simba.lol/storage/lembaga/logo/test.png",
                "kop_url": "https://simba.lol/storage/lembaga/kop/test.jpg",
                "ttd_url": "https://simba.lol/storage/lembaga/ttd/test.png",
                "stempel_url": "https://simba.lol/storage/lembaga/stempel/test.png",
                "maps_embed_url": "https://maps.google.com/maps?q=Jxxx&hl=id&z=15&output=embed",
            },
        }
    },
    "server_time": "2026-11-16T10:19:58+07:00"
}
JSON;

    Livewire::test(SettingForm::class)
        ->set('json_text', $jsonSampleWithTrailingComma)
        ->call('processImportJson')
        ->assertHasNoErrors()
        ->assertSet('showImportModal', false);

    $setting = Setting::where('key', 'lembaga')->first();
    expect($setting)->not->toBeNull();
    expect($setting->payload)->toHaveKey('1');
    expect($setting->payload[1]['nama'])->toEqual('SDI Al rafa');
    expect($setting->payload[1]['singkatan'])->toEqual('SDI');
    expect($setting->payload[1]['npsn'])->toEqual('69981912');
    expect($setting->payload[1]['kota'])->toEqual('Blora');
    expect($setting->payload[1]['tingkat'])->toEqual(['1', '2', '3', '4', '5', '6']);
});

it('dapat mengimpor data lembaga melalui unggah berkas file json', function () {
    $this->actingAs($this->admin);

    $jsonData = json_encode([
        'status' => 'success',
        'data' => [
            'lembaga' => [
                '2' => [
                    'nama' => 'SMP IT Bintang',
                    'singkatan' => 'SMPIT',
                    'alamat' => 'Jl. Pendidikan No 10',
                    'kontak' => 'Ustadz Ahmad',
                    'telp' => '08123456789',
                    'tingkat' => ['7', '8', '9'],
                ],
            ],
        ],
    ]);

    $file = UploadedFile::fake()->createWithContent('lembaga_backup.json', $jsonData);

    Livewire::test(SettingForm::class)
        ->set('json_file', $file)
        ->call('processImportJson')
        ->assertHasNoErrors()
        ->assertSet('showImportModal', false);

    $setting = Setting::where('key', 'lembaga')->first();
    expect($setting)->not->toBeNull();
    expect($setting->payload)->toHaveKey('2');
    expect($setting->payload[2]['nama'])->toEqual('SMP IT Bintang');
    expect($setting->payload[2]['singkatan'])->toEqual('SMPIT');
});

it('menampilkan pesan error jika format json tidak valid', function () {
    $this->actingAs($this->admin);

    Livewire::test(SettingForm::class)
        ->set('json_text', '{ ini bukan json yang valid }')
        ->call('processImportJson')
        ->assertHasErrors(['import_error']);
});

it('menampilkan pesan error jika file dan teks kosong saat impor diproses', function () {
    $this->actingAs($this->admin);

    Livewire::test(SettingForm::class)
        ->call('processImportJson')
        ->assertHasErrors(['import_error']);
});
