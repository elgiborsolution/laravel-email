<?php

namespace ESolution\LaravelEmail\Support;

use ESolution\LaravelEmail\Contracts\MailDriver;
use ESolution\LaravelEmail\Drivers\SendGridDriver;
use InvalidArgumentException;

class MailManager
{
    protected array $config;
    protected array $drivers = [];
    protected array $keys;
    protected int $cursor = 0;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->keys = array_keys($config['providers'] ?? []);
    }

    public function driver(?string $name = null): MailDriver
    {
        $name = $name ?: $this->pickProviderKey();
        if (!isset($this->drivers[$name])) {
            $this->drivers[$name] = $this->resolve($name);
        }
        return $this->drivers[$name];
    }

    protected function resolve(string $name): MailDriver
    {
        $providers = $this->config['providers'] ?? [];
        if (!isset($providers[$name])) {
            throw new InvalidArgumentException("Provider [$name] is not configured.");
        }
        $conf = $providers[$name];
        return match($conf['driver'] ?? null) {
            'sendgrid' => new SendGridDriver($conf),
            default => throw new InvalidArgumentException("Unsupported driver ".($conf['driver']??'null')),
        };
    }

    protected function pickProviderKey(): string
    {
        $strategy = $this->config['strategy'] ?? 'fixed';
        if ($strategy === 'round_robin' && !empty($this->keys)) {
            $key = $this->keys[$this->cursor % count($this->keys)];
            $this->cursor++;
            return $key;
        }
        return $this->config['default_provider'] ?? $this->keys[0];
    }
}
