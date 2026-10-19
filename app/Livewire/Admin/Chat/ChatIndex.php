<?php

namespace App\Livewire\Admin\Chat;

use App\Models\ChatMessage;
use App\Models\Siswa;
use Livewire\Component;
use Livewire\WithFileUploads;

class ChatIndex extends Component
{
    use WithFileUploads;

    public ?string $selectedSiswaId = null;

    public string $pesan = '';

    public $attachment = null;

    public string $search = '';

    public bool $filterUnreadOnly = false;

    protected $queryString = [
        'selectedSiswaId' => ['except' => null, 'as' => 'siswa'],
    ];

    public function mount(): void
    {
        if (request()->query('siswa')) {
            $this->selectedSiswaId = (string) request()->query('siswa');
        }

        if ($this->selectedSiswaId) {
            $this->markSelectedAsRead();
        }
    }

    public function selectSiswa(string $siswaId): void
    {
        $this->selectedSiswaId = $siswaId;
        $this->markSelectedAsRead();
        $this->dispatch('chat-selected');
    }

    public function markSelectedAsRead(): void
    {
        if (! $this->selectedSiswaId) {
            return;
        }

        ChatMessage::where('siswa_id', $this->selectedSiswaId)
            ->where('sender_type', 'siswa')
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
        if (($clean === '' && ! $this->attachment) || ! $this->selectedSiswaId) {
            return;
        }

        $this->validate([
            'pesan' => 'nullable|string|max:2000',
            'attachment' => 'nullable|file|mimes:jpeg,png,jpg,webp,gif,pdf|max:10240',
        ]);

        $attachmentData = [];
        if ($this->attachment) {
            $path = $this->attachment->store("chat-attachments/{$this->selectedSiswaId}", 'public');
            $attachmentData = [
                'attachment_path' => $path,
                'attachment_name' => $this->attachment->getClientOriginalName(),
                'attachment_type' => $this->attachment->getMimeType(),
                'attachment_size' => $this->attachment->getSize(),
            ];
        }

        ChatMessage::create(array_merge([
            'siswa_id' => $this->selectedSiswaId,
            'sender_type' => 'admin',
            'sender_id' => auth()->id(),
            'pesan' => $clean !== '' ? $clean : null,
            'is_read' => false,
        ], $attachmentData));

        $this->reset(['pesan', 'attachment']);
        $this->dispatch('message-sent');
    }

    public function sendQuickReply(string $text): void
    {
        $this->pesan = $text;
        $this->sendMessage();
    }

    public function render()
    {
        // 1. Fetch conversations list
        $conversationsQuery = Siswa::query()
            ->with(['kelas', 'latestChatMessage'])
            ->withCount(['chatMessages as unread_count' => function ($q) {
                $q->where('sender_type', 'siswa')->where('is_read', false);
            }]);

        if (! empty($this->search)) {
            $term = trim($this->search);
            $conversationsQuery->where(function ($q) use ($term) {
                $q->where('nama', 'like', "%{$term}%")
                    ->orWhere('nis', 'like', "%{$term}%")
                    ->orWhere('nisn', 'like', "%{$term}%");
            });
        } else {
            // Only show students who have chatted
            $conversationsQuery->whereHas('chatMessages');
        }

        if ($this->filterUnreadOnly) {
            $conversationsQuery->has('chatMessages', '>=', 1)
                ->whereHas('chatMessages', function ($q) {
                    $q->where('sender_type', 'siswa')->where('is_read', false);
                });
        }

        // Order by latest message
        $conversations = $conversationsQuery->get()
            ->sortByDesc(function ($s) {
                return $s->latestChatMessage?->created_at ?? $s->updated_at;
            })
            ->values();

        // 2. Fetch active chat messages if student selected
        $selectedSiswa = null;
        $activeMessages = collect();

        if ($this->selectedSiswaId) {
            $selectedSiswa = Siswa::with('kelas')->find($this->selectedSiswaId);
            if ($selectedSiswa) {
                $this->markSelectedAsRead();
                $activeMessages = ChatMessage::with('sender.authable')
                    ->where('siswa_id', $this->selectedSiswaId)
                    ->orderBy('created_at', 'asc')
                    ->get();
            } else {
                $this->selectedSiswaId = null;
            }
        }

        $totalUnread = ChatMessage::where('sender_type', 'siswa')->where('is_read', false)->count();

        return view('livewire.admin.chat.chat-index', [
            'conversations' => $conversations,
            'selectedSiswa' => $selectedSiswa,
            'activeMessages' => $activeMessages,
            'totalUnread' => $totalUnread,
        ]);
    }
}
