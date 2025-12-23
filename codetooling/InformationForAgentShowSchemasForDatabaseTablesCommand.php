<?php

namespace CodeTooling;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

    class InformationForAgentShowSchemasForDatabaseTablesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'information-for-agent:show-schemas-for-database-tables {tableNamesSeparatedByComma}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $schemasString = str($this->argument('tableNamesSeparatedByComma'))
            ->explode(",")
            ->map(fn(string $table) => trim($table))
            ->map(fn(string $table) => DB::select("SHOW CREATE TABLE {$table}")[0]->{"Create Table"})
            ->implode("\n\n")
        ;

        echo($schemasString);
    }
}
