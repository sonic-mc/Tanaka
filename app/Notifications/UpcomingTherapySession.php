<?php

namespace App\Notifications;

use App\Models\TherapySession;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class UpcomingTherapySession extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public TherapySession $session)
    {
        //
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $patient = $this->session->patient;
        $patientName = $patient
            ? trim(($patient->first_name ?? '') . ' ' . ($patient->last_name ?? '')) ?: ($patient->name ?? 'Patient')
            : 'Patient';

        return [
            'type' => 'reminder',
            'title' => 'Therapy session reminder',
            'subtitle' => sprintf('%s — %s',
                $patientName,
                optional($this->session->session_start)->format('M d, g:i A')
            ),
            'icon' => 'fa-bell',
            'color' => 'text-warning',
            'link' => route('therapy-sessions.show', $this->session->id),
            'session_id' => $this->session->id,
            'session_start' => optional($this->session->session_start)?->toIso8601String(),
            'patient_id' => $this->session->patient_id,
        ];
    }
}