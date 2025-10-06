<?php

namespace ESolution\LaravelEmail\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use ESolution\LaravelEmail\Models\{BroadcastRecipient, EmailEvent, Suppression};

class WebhookController extends Controller
{
    public function sendgrid(Request $r)
    {
        $events = $r->json()->all();
        if (!is_array($events)) return response()->json(['ok'=>true]);

        foreach ($events as $e) {
            $event = $e['event'] ?? null;
            $recipientId = (int)($e['recipient_id'] ?? 0);
            $token = $e['recipient_token'] ?? null;
            $email = $e['email'] ?? null;

            if (!$recipientId && $token) {
                $row = DB::table('le_broadcast_recipients')->select('id','email')->where('token', $token)->first();
                if ($row) { $recipientId = (int)$row->id; $email = $email ?: $row->email; }
            }

            EmailEvent::create([
                'broadcast_id' => (int)($e['broadcast_id'] ?? 0) ?: null,
                'recipient_id' => $recipientId ?: null,
                'event' => $event ?: 'unknown',
                'provider' => 'sendgrid',
                'payload' => $e,
            ]);

            if (in_array($event, ['bounce','dropped','spamreport','unsubscribe']) && $email) {
                Suppression::updateOrCreate(
                    ['email' => strtolower($email)],
                    ['reason' => $event === 'spamreport' ? 'spam' : ($event === 'dropped' ? 'bounce' : $event)]
                );
                // also mark on recipient row
                if ($recipientId) {
                    $q = DB::table('le_broadcast_recipients')->where('id',$recipientId);
                    if ($event === 'unsubscribe' || $event === 'spamreport') $q->update(['unsubscribed_at' => now()]);
                    if ($event === 'bounce' || $event === 'dropped') $q->update(['bounced_at' => now()]);
                }
            }
        }

        return response()->json(['ok'=>true]);
    }
}
