<?php

namespace GregPriday\ZyteApi\Commands;

use Illuminate\Console\Command;

class ZyteApiCommand extends Command
{
    public $signature = 'laravel-zyte-api';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
