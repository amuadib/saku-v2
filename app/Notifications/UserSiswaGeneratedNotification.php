<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class UserSiswaGeneratedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public int $count,
        public int $total
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Generate Akun Siswa Selesai',
            'message' => $this->count > 0
                ? "Berhasil membuat {$this->count} user siswa baru."
                : "Semua siswa aktif ({$this->total}) sudah memiliki akun user.",
            'count' => $this->count,
            'total' => $this->total,
            'time' => now()->toDateTimeString(),
        ];
    }
}
