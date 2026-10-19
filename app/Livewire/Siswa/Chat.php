<?php

namespace App\Livewire\Siswa;

use App\Models\ChatMessage;
use App\Models\Siswa;
use Livewire\Component;
use Livewire\WithFileUploads;

class Chat extends Component
{
    use WithFileUploads;

    public $pesan = '';

    public $attachment = null;

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

    public function cancelAttachment(): void
    {
        $this->reset('attachment');
    }

    public function sendMessage(): void
    {
        $clean = trim((string) $this->pesan);
        if (($clean === '' && ! $this->attachment) || ! $this->siswa) {
            return;
        }

        $this->validate([
            'pesan' => 'nullable|string|max:2000',
            'attachment' => 'nullable|file|mimes:jpeg,png,jpg,webp,gif,pdf|max:10240',
        ]);

        $attachmentData = [];
        if ($this->attachment) {
            $path = $this->attachment->store("chat-attachments/{$this->siswa->id}", 'public');
            $attachmentData = [
                'attachment_path' => $path,
                'attachment_name' => $this->attachment->getClientOriginalName(),
                'attachment_type' => $this->attachment->getMimeType(),
                'attachment_size' => $this->attachment->getSize(),
            ];
        }

        ChatMessage::create(array_merge([
            'siswa_id' => $this->siswa->id,
            'sender_type' => 'siswa',
            'sender_id' => auth()->id(),
            'pesan' => $clean !== '' ? $clean : null,
            'is_read' => false,
        ], $attachmentData));

        $this->reset(['pesan', 'attachment']);
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
