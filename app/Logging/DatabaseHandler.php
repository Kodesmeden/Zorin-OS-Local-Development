<?php

namespace App\Logging;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;
use Illuminate\Support\Facades\DB;

class DatabaseHandler extends AbstractProcessingHandler
{
    protected function write(LogRecord $record): void
    {
        $level = $record->level->getName();
        $context = $record->context ?? [];
        $handle = $context['handle'] ?? strtolower($level);

        unset($context['handle']);

        DB::table('logs')->insert([
            'handle' => $handle,
            'level' => $level,
            'message' => $record->message,
            'context' => json_encode($context),
            'created_at' => now(),
        ]);
    }
}