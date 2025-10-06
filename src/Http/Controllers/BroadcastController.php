<?php

namespace ESolution\LaravelEmail\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use ESolution\LaravelEmail\Models\{Broadcast, BroadcastRecipient};
use ESolution\LaravelEmail\Jobs\SendEmailJob;

class BroadcastController extends Controller
{
    public function create(Request $r)
    {
        $data = $r->validate([
            'name' => 'required|string',
            'template_id' => 'required|integer|exists:le_email_templates,id',
            'provider_key' => 'nullable|string',
            'headers' => 'nullable|array',
            'custom_args' => 'nullable|array',
        ]);
        $data['status'] = 'draft';
        return Broadcast::create($data);
    }

    public function addRecipients(Request $r, Broadcast $broadcast)
    {
        $payload = $r->validate([
            'recipients' => 'required|array',
            'recipients.*.email' => 'required|email',
            'recipients.*.name' => 'nullable|string',
        ]);

        $rows = [];
        foreach ($payload['recipients'] as $item) {
            $rows[] = [
                'broadcast_id' => $broadcast->id,
                'email' => strtolower($item['email']),
                'name' => $item['name'] ?? null,
                'token' => (string) Str::uuid(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        // Upsert by token (unique)
        DB::table('le_broadcast_recipients')->upsert(
            $rows,
            ['token'],
            ['email','name','updated_at']
        );
        return response()->json(['added' => count($rows)]);
    }

    public function start(Request $r, Broadcast $broadcast)
    {
        $rpm = (int) config('laravel_email.rate_limit_per_minute', 600);
        $delaySeconds = 60 / max(1, $rpm);

        $broadcast->update(['status' => 'queued']);

        $i = 0;
        foreach (BroadcastRecipient::where('broadcast_id', $broadcast->id)->whereNull('sent_at')->cursor() as $rcpt) {
            SendEmailJob::dispatch($broadcast->id, $rcpt->id)->delay(now()->addSeconds((int)($i*$delaySeconds)));
            $i++;
        }
        $broadcast->update(['status' => 'sending']);
        return response()->json(['queued' => $i]);
    }
}
