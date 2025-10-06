<?php

namespace ESolution\LaravelEmail\Drivers;

use ESolution\LaravelEmail\Contracts\MailDriver;
use SendGrid;
use SendGrid\Mail\Mail;
use Throwable;

class SendGridDriver implements MailDriver
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function send(array $message): array
    {
        $email = new Mail();
        $from = $message['from'] ?? [
            'email' => $this->config['from_email'] ?? 'no-reply@example.com',
            'name'  => $this->config['from_name']  ?? 'No Reply',
        ];
        $email->setFrom($from['email'], $from['name'] ?? null);
        $email->setSubject($message['subject'] ?? '');

        foreach (($message['to'] ?? []) as $rcpt) {
            $email->addTo($rcpt['email'], $rcpt['name'] ?? null);
        }

        if (!empty($message['html'])) $email->addContent('text/html', $message['html']);
        if (!empty($message['text'])) $email->addContent('text/plain', $message['text']);

        if (!empty($message['headers'])) {
            foreach ($message['headers'] as $k=>$v) $email->addHeader($k, $v);
        }

        if (!empty($message['custom_args'])) {
            foreach ($message['custom_args'] as $k=>$v) $email->addCustomArg($k, (string)$v);
        }

        if (!empty($this->config['sandbox_mode'])) {
            $email->setMailSettings([ 'sandbox_mode' => ['enable' => true] ]);
        }

        $sg = new SendGrid($this->config['api_key'] ?? '');
        try {
            $resp = $sg->send($email);
            $headers = $resp->headers();
            $mid = $headers['X-Message-Id'] ?? $headers['X-Message-ID'] ?? null;
            return ['provider_message_id' => is_array($mid) ? ($mid[0]??null) : $mid];
        } catch (Throwable $e) {
            return ['provider_message_id' => null];
        }
    }
}
