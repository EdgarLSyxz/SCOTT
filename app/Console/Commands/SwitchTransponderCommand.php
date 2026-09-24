<?php

namespace App\Console\Commands;

use App\Models\Transponder;
use Illuminate\Console\Command;

class SwitchTransponderCommand extends Command
{
    protected $signature = 'modulators:switch
                            {code : Transponder code, e.g. KU01}
                            {from : Source city to power OFF}
                            {to : Destination city to power ON}';

    protected $description = 'Sends power-OFF to the source city and power-ON to the destination city for a given transponder (demo mode).';

    public function handle(): int
    {
        $code = strtoupper(trim((string) $this->argument('code')));
        $from = trim((string) $this->argument('from'));
        $to = trim((string) $this->argument('to'));

        $transponder = Transponder::where('code', $code)->first();
        if (! $transponder) {
            $this->error("[modulators] Transponder '{$code}' not found.");

            return self::FAILURE;
        }

        $timestamp = now()->format('Y-m-d H:i:s');
        $offCmd = sprintf('TX %s POWER OFF @ %s', $code, $from);
        $onCmd = sprintf('TX %s POWER ON  @ %s', $code, $to);

        $this->line("[{$timestamp}] {$offCmd}");
        $this->line("[{$timestamp}] {$onCmd}");

        \Illuminate\Support\Facades\Log::info('modulator.command', [
            'transponder' => $code,
            'from' => $from,
            'to' => $to,
            'commands' => [$offCmd, $onCmd],
        ]);

        return self::SUCCESS;
    }
}
