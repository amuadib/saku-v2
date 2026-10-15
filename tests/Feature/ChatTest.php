<?php

use App\Livewire\Admin\Chat\ChatIndex;
use App\Livewire\Siswa\Chat;
use App\Models\ChatMessage;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('siswa can access chat page and send a message', function () {
    $siswa = Siswa::create([
        'nis' => '12345',
        'nama' => 'Budi Santoso',
        'status' => 1,
        'lembaga_id' => 1,
        'jenis_kelamin' => 'l',
    ]);

    $user = User::factory()->create([
        'role_id' => 5,
        'authable_type' => Siswa::class,
        'authable_id' => $siswa->id,
    ]);

    $this->actingAs($user)
        ->get(route('siswa.chat'))
        ->assertOk();

    Livewire::actingAs($user)
        ->test(Chat::class)
        ->set('pesan', 'Halo admin, mau tanya tagihan')
        ->call('sendMessage')
        ->assertDispatched('message-sent');

    expect(ChatMessage::where('siswa_id', $siswa->id)->count())->toBe(1);

    $msg = ChatMessage::where('siswa_id', $siswa->id)->first();
    expect($msg->pesan)->toBe('Halo admin, mau tanya tagihan');
    expect($msg->sender_type)->toBe('siswa');
    expect($msg->is_read)->toBeFalse();
});

test('admin can access admin chat and reply to siswa', function () {
    $siswa = Siswa::create([
        'nis' => '12345',
        'nama' => 'Budi Santoso',
        'status' => 1,
        'lembaga_id' => 1,
        'jenis_kelamin' => 'l',
    ]);

    ChatMessage::create([
        'siswa_id' => $siswa->id,
        'sender_type' => 'siswa',
        'pesan' => 'Pertanyaan siswa',
        'is_read' => false,
    ]);

    $adminUser = User::factory()->create([
        'role_id' => 1,
    ]);

    $this->actingAs($adminUser)
        ->get(route('admin.chat.index'))
        ->assertOk();

    Livewire::actingAs($adminUser)
        ->test(ChatIndex::class)
        ->call('selectSiswa', $siswa->id)
        ->assertSet('selectedSiswaId', $siswa->id)
        ->set('pesan', 'Baik, pesan Anda sudah kami terima.')
        ->call('sendMessage')
        ->assertDispatched('message-sent');

    expect(ChatMessage::where('siswa_id', $siswa->id)->count())->toBe(2);

    $reply = ChatMessage::where('siswa_id', $siswa->id)->where('sender_type', 'admin')->first();
    expect($reply->pesan)->toBe('Baik, pesan Anda sudah kami terima.');

    // The student message should now be marked as read
    $studentMsg = ChatMessage::where('siswa_id', $siswa->id)->where('sender_type', 'siswa')->first();
    expect($studentMsg->is_read)->toBeTrue();
});

test('student chat displays admin sender name on replied messages', function () {
    $siswa = Siswa::create([
        'nis' => '12345',
        'nama' => 'Budi Santoso',
        'status' => 1,
        'lembaga_id' => 1,
        'jenis_kelamin' => 'l',
    ]);

    $studentUser = User::factory()->create([
        'role_id' => 5,
        'authable_type' => Siswa::class,
        'authable_id' => $siswa->id,
    ]);

    $adminUser = User::factory()->create([
        'username' => 'pak_budi_tu',
        'role_id' => 1,
    ]);

    ChatMessage::create([
        'siswa_id' => $siswa->id,
        'sender_type' => 'admin',
        'sender_id' => $adminUser->id,
        'pesan' => 'Halo Budi, silakan datang ke TU',
        'is_read' => false,
    ]);

    Livewire::actingAs($studentUser)
        ->test(Chat::class)
        ->assertSeeHtml($adminUser->name);
});

test('unread chat count is correctly calculated and displayed in sidebar', function () {
    $siswa = Siswa::create([
        'nis' => '12345',
        'nama' => 'Budi Santoso',
        'status' => 1,
        'lembaga_id' => 1,
        'jenis_kelamin' => 'l',
    ]);

    $adminUser = User::factory()->create([
        'role_id' => 1,
    ]);

    expect(ChatMessage::unreadCountForUser($adminUser))->toBe(0);

    ChatMessage::create([
        'siswa_id' => $siswa->id,
        'sender_type' => 'siswa',
        'pesan' => 'Pesan 1',
        'is_read' => false,
    ]);
    ChatMessage::create([
        'siswa_id' => $siswa->id,
        'sender_type' => 'siswa',
        'pesan' => 'Pesan 2',
        'is_read' => false,
    ]);
    ChatMessage::create([
        'siswa_id' => $siswa->id,
        'sender_type' => 'admin',
        'pesan' => 'Pesan admin',
        'is_read' => false,
    ]);

    expect(ChatMessage::unreadCountForUser($adminUser))->toBe(2);

    $response = $this->actingAs($adminUser)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Chat Siswa');

    // The badge text '2' should be in the rendered HTML
    expect($response->getContent())->toContain('>2<');
});

test('unread chat count for siswa counts unread admin messages', function () {
    $siswa = Siswa::create([
        'nis' => '12345',
        'nama' => 'Budi Santoso',
        'status' => 1,
        'lembaga_id' => 1,
        'jenis_kelamin' => 'l',
    ]);

    $studentUser = User::factory()->create([
        'role_id' => 5,
        'authable_type' => Siswa::class,
        'authable_id' => $siswa->id,
    ]);

    expect(ChatMessage::unreadCountForUser($studentUser))->toBe(0);

    ChatMessage::create([
        'siswa_id' => $siswa->id,
        'sender_type' => 'siswa',
        'pesan' => 'Pesan siswa',
        'is_read' => false,
    ]);
    ChatMessage::create([
        'siswa_id' => $siswa->id,
        'sender_type' => 'admin',
        'pesan' => 'Balasan admin',
        'is_read' => false,
    ]);

    expect(ChatMessage::unreadCountForUser($studentUser))->toBe(1);

    $this->actingAs($studentUser)
        ->get(route('siswa.dashboard'))
        ->assertOk()
        ->assertSee('animate-pulse');
});
