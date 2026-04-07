<?php

namespace App\Services;

class JournalService
{
    public function record(Journalable $entity, string $event): void
    {
        \DB::table('finance_journals')->insert([
            'account_id' => $entity->journalAccount(),
            'event' => $event,
            'payload' => json_encode($entity->journalPayload()),
            'created_at' => now(),
        ]);
    }
}
