<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FreshDatabaseDaily extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:fresh-database-daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->call('migrate:fresh', [
            '--seed'  => true,
            '--force' => true,
        ]);

        return Command::SUCCESS;
    }
}
