<?php

namespace CodeTooling;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

    class InformationForAgentShowListOfDatabaseTablesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'information-for-agent:list-database-tables';

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
        $tables = collect(DB::select("SHOW TABLE STATUS"))
            ->pluck('Name')
            ->implode("\n");

        echo($tables);
    }
}
