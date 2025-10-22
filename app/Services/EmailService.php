<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmailService
{
    protected string $baseUrl = 'http://167.86.88.166:9104/notification-service/emails';

    public function sendEmailWithAttachment(string $recipientEmail, string $subject, string $body, array $attachments): array
    {
        $payload = [
            'recipientEmailAddress' => $recipientEmail,
            'subject' => $subject,
            'messageBodyText' => $body,
            'attachments' => array_map(function ($attachment) {
                return [
                    'theAttachmentBytes' => [$attachment['base64']],
                    'contentType' => $attachment['contentType'],
                    'filename' => $attachment['filename'],
                ];
            }, $attachments),
        ];

        return $this->post('/send-email-with-attachment', $payload);
    }

    public function sendBulkEmails(array $emails): array
    {
        $payload = [
            'emails' => array_map(function ($email) {
                return [
                    'filename' => $email['filename'],
                    'myBytes' => [$email['base64']],
                    'receipient' => [
                        'fullname' => $email['fullname'],
                        'email' => $email['email'],
                    ],
                    'subject' => $email['subject'],
                    'contentType' => $email['contentType'],
                    'bodyText' => $email['bodyText'],
                ];
            }, $emails),
        ];

        return $this->post('/send', $payload);
    }

    protected function post(string $endpoint, array $payload): array
    {
        try {
            $response = Http::acceptJson()
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($this->baseUrl . $endpoint, $payload);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            Log::error("EmailService error", [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'error' => 'Email service returned error: ' . $response->status(),
            ];
        } catch (\Exception $e) {
            Log::error("EmailService exception", ['message' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => 'Exception: ' . $e->getMessage(),
            ];
        }
    }
}
