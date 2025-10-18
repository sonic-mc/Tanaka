<?php

namespace App\Services;

use Illuminate\Notifications\DatabaseNotification;
use App\Models\User;
use Illuminate\Support\Collection;

class DashboardNotificationService
{
    /**
     * Fetch recent notifications for a user (database notifications).
     * Returns a normalized array for the dashboard card.
     */
    public function getForUser(User $user, int $limit = 10): Collection
    {
        return $user->notifications()
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function (DatabaseNotification $n) {
                $data = $n->data ?? [];
                return [
                    'id' => $n->id,
                    'type' => $data['type'] ?? class_basename($n->type),
                    'title' => $data['title'] ?? 'Notification',
                    'subtitle' => $data['subtitle'] ?? ($data['body'] ?? null),
                    'icon' => $data['icon'] ?? 'fa-info-circle',
                    'color' => $data['color'] ?? 'text-muted',
                    'link' => $data['link'] ?? ($data['url'] ?? null),
                    'time' => $n->created_at,
                    'unread' => is_null($n->read_at),
                ];
            });
    }
}
