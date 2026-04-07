<?php

namespace App\Contracts;

interface Journalable
{
    public function journalPayload(): array;

    public function journalAccount(): int; // account_id
}
