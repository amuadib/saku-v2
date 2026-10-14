<?php

namespace App\Livewire\Siswa;

use App\Models\ChatMessage;
use App\Models\Siswa;
use Livewire\Component;

class Chat extends Component
{
    public $pesan = '';

    public ?Siswa $siswa = null;

    public function mount()
    {
        $user = auth()->user();
        if ($user && $user->authable instanceof Siswa) {
            $this->siswa = $user->authable;
        } elseif ($user && $user->authable_type === Siswa::class) {
            $this->siswa = Siswa::find($user->authable_id);
        }

        $this->markAdminMessagesAsRead();
    }

    public function markAdminMessagesAsRead(): void
    {
        if (! $this->siswa) {
            return;
        }

        ChatMessage::where('siswa_id', $this->siswa->id)
            ->where('sender_type', 'admin')
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    public function sendMessage(): void
    {
        $clean = trim((string) $this->pesan);
        if ($clean === '' || ! $this->siswa) {
            return;
        }

        $this->validate([
            'pesan' => 'required|string|max:2000',
        ]);

        ChatMessage::create([
            'siswa_id' => $this->siswa->id,
            'sender_type' => 'siswa',
            'sender_id' => auth()->id(),
            'pesan' => $clean,
            'is_read' => false,
        ]);

        $this->reset('pesan');
        $this->dispatch('message-sent');
    }

    public function sendQuickMessage(string $text): void
    {
        $this->pesan = $text;
        $this->sendMessage();
    }

    public function render()
    {
        $messages = collect();

        if ($this->siswa) {
            $this->markAdminMessagesAsRead();

            $messages = ChatMessage::with('sender.authable')
                ->where('siswa_id', $this->siswa->id)
                ->orderBy('created_at', 'asc')
                ->get();
        }

        return view('livewire.siswa.chat', [
            'messages' => $messages,
        ])->layout('layouts.siswa', ['title' => 'Chat Bantuan']);
    }
}
