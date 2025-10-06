<?php

namespace ESolution\LaravelEmail\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use ESolution\LaravelEmail\Models\{Broadcast, BroadcastRecipient, EmailTemplate, Suppression};
use ESolution\LaravelEmail\Support\MailManager;
use Carbon\Carbon;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $broadcastId;
    public int $recipientId;

    public function __construct(int $broadcastId, int $recipientId)
    {
        $this->broadcastId = $broadcastId;
        $this->recipientId = $recipientId;
    }

    public function handle(MailManager $manager): void
    {
        $broadcast = Broadcast::findOrFail($this->broadcastId);
        $rcpt = BroadcastRecipient::findOrFail($this->recipientId);

        // Suppression check (global)
        if (Suppression::where('email', strtolower($rcpt->email))->exists()) {
            return; // skip sending
        }

        $tpl = EmailTemplate::findOrFail($broadcast->template_id);

        $trackUrl = route('laravel_email.track', ['token' => $rcpt->token]);
        $unsubscribeUrl = route('laravel_email.unsubscribe', ['token' => $rcpt->token]);

        $html = str_replace(
            ['{{name}}','{{email}}','{{unsubscribe_url}}','{{tracking_pixel}}'],
            [
                $rcpt->name ?? '',
                $rcpt->email,
                $unsubscribeUrl,
                '<img src="'.$trackUrl.'" alt="" width="1" height="1" style="display:none">'
            ],
            $tpl->html ?? ''
        );

        $text = str_replace(
            ['{{name}}','{{email}}','{{unsubscribe_url}}'],
            [$rcpt->name ?? '', $rcpt->email, $unsubscribeUrl],
            $tpl->text ?? ''
        );

        $headers = $broadcast->headers ?? [];
        if (config('laravel_email.list_unsubscribe')) {
            $headers['List-Unsubscribe'] = '<'.$unsubscribeUrl.'>';
        }

        $message = [
            'from' => [
                'email' => $tpl->from_email ?: config('mail.from.address'),
                'name'  => $tpl->from_name ?: config('mail.from.name'),
            ],
            'to' => [['email' => $rcpt->email, 'name' => $rcpt->name]],
            'subject' => $tpl->subject,
            'html' => $html,
            'text' => $text,
            'headers' => $headers,
            'custom_args' => array_merge($broadcast->custom_args ?? [], [
                'broadcast_id' => $broadcast->id,
                'recipient_id' => $rcpt->id,
                'recipient_token' => $rcpt->token,
            ]),
        ];

        $driver = $manager->driver($broadcast->provider_key);
        $resp = $driver->send($message);

        $rcpt->sent_at = Carbon::now();
        $rcpt->provider_message_id = $resp['provider_message_id'] ?? null;
        $rcpt->save();
    }
}
