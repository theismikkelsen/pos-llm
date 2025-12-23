<?php

namespace CodeTooling;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

    class InformationForAgentShowMigrationsThatHaveBeenRunCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'information-for-agent:show-database-migrations-that-have-been-run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $tables = DB::table('migrations')
            ->pluck('migration')
            ->implode("\n");

        echo($tables);
    }
}
