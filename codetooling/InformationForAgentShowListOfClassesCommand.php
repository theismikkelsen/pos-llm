<?php

namespace CodeTooling;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class InformationForAgentShowListOfClassesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'information-for-agent:list-classes  {--type=}';

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
        $path = base_path('app');
        if (!is_dir($path)) {
            $this->error("The specified path is not a directory");
            return;
        }

        $directory = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new RecursiveIteratorIterator($directory);

        $numberOfFilesChecked = 0;

        $result = [];
        foreach ($iterator as $info) {
            $filePath = str($info->getPathname())->replaceFirst($path, 'App');
            $result[] = $filePath;
        }

        $result = collect($result);

        if ($this->option('type')!==NULL) {
            $result = match ($this->option('type')) {
                'repository' => $result->filter(fn(string $filePath) => str_ends_with($filePath, 'Repository.php')),
                'controller' => $result->filter(fn(string $filePath) => str_ends_with($filePath, 'Controller.php') && str_ends_with($filePath, '\Controller.php')===FALSE),
                'all' => $result,
                default => throw new \Exception("Invalid type specified: {$this->option('type')}. Valid types: repository"),
            };
        }

        echo($result->implode("\n"));
    }
}
