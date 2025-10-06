<?php

namespace ESolution\LaravelEmail\Contracts;

interface MailDriver
{
    public function send(array $message): array;
}
