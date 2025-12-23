<?php

namespace CodeTooling;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class UpdateWithMethodOnClassesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'code-tooling:update-with-method-on-classes';

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
            $filePath = $info->getPathname();
            $this->handleSpecificFile($filePath);
        }
    }

    public function handleSpecificFile(string $file)
    {
        $fileContents = file_get_contents($file);

        $oldMethod = $this->identifyOldMethod($fileContents);

        if ($oldMethod===NULL) {
            return;
        }

        if (str_contains($fileContents, $oldMethod)===FALSE) {
            throw new \Exception('Old method not found.');
        }

        $newMethod = $this->makeNewMethod($fileContents);

        if ($oldMethod==$newMethod) {
            return;
        }

        $confirmMessage = <<<MESSAGE
        Do you want to proceed with updating with-method on classes?

        ## Old method
        $oldMethod

        ## New method
        $newMethod


        MESSAGE;

        if ($this->confirm($confirmMessage)) {
            file_put_contents($file, str_replace($oldMethod, $newMethod, $fileContents));
            $this->info('Successfully updated with-method in TestClass.php');
        } else {
            $this->info('Operation cancelled - no changes were made');
        }
    }

    public function makeNewMethod(
        string $fileContents
    ): string {
        $balance = 0;
        $isFirstLine = true;

        $constructorParametersForWithMethodLines = Str::of($fileContents)
            ->explode("\n")
            ->map(fn(string $line) => trim($line))
            ->reject(fn(string $line) => $line=='' || ctype_space($line))
            ->reject(fn(string $line) => str_starts_with($line, '#['))
            ->reject(fn(string $line) => str_starts_with($line, '//'))
            ->reject(fn(string $line) => str_starts_with($line, '/*'))
            ->skipWhile(fn(string $line) => str_starts_with($line, 'public function __construct(')===FALSE)
            ->takeWhile(function ($line) use (&$balance, &$isFirstLine) {
                $previousBalance = $balance;

                $balance += substr_count($line, '(') - substr_count($line, ')');

                if ($isFirstLine) {
                    $isFirstLine = false;
                    return true;
                }

                return $balance > 0 || $previousBalance > 0;
            })
            ->values()
            ->pipe(function (Collection $lines) {
                $lastLine = Str::of($lines->last())->before('{')->trim()->value();
                $lines[array_key_last($lines->all())] = $lastLine;
                return $lines;
            })
            ->pipe(function (Collection $lines) {
                return Str::of($lines->implode(""))
                    ->replaceStart('public function __construct(', '')
                    ->replaceEnd(')', '')
                    ->explode(",")
                    ->reject(fn(string $line) => $line=='' || ctype_space($line));
            })
            ->map(fn(string $line) => explode(',', $line))
            ->flatten(1)
            ->map(fn(string $line) => trim($line))
            ->reject(fn(string $line) => $line=='' || ctype_space($line))
            ->map(function (string $line) {
                if (str_starts_with($line, 'public function __construct(')) {
                    return $line;
                }

                return Str::of($line)
                    ->replaceStart('public', '')
                    ->replaceStart('private', '')
                    ->replaceStart('protected', '')
                    ->trim()
                    ->value()
                    ;
            })
            ->map(function (string $line) {
                if (str_starts_with($line, 'public function __construct(')) {
                    return $line;
                }

                return Str::of($line)
                    ->replaceStart('readonly', '')
                    ->trim()
                    ->value()
                    ;
            })
            ->map(fn(string $line) => Str::of($line)->replaceStart('?', 'NULL|')->value())
            ->map(fn(string $line) => str_starts_with($line, 'mixed')
                ? $line
                : '\CodeTooling\OmittedArg|'.$line
            )
            ->map(fn(string $line) => Str::of($line)->before('=')->trim()->value())
            ->map(fn(string $line) => "$line = new \CodeTooling\OmittedArg");

        $phpDocForWithMethodLines = Str::of($fileContents)
            ->explode("\n")
            ->map(fn(string $line) => trim($line))
            ->reject(fn(string $line) => $line=='' || ctype_space($line))
            ->reverse()
            ->values()
            ->skipWhile(fn(string $line) => str_starts_with($line, 'public function __construct(')===FALSE)
            ->values()
            ->pipe(function (Collection $lines) {
                if (str_starts_with($lines[1], '*/')===FALSE) {
                    return collect();
                }
                return $lines;
            })
            ->skipWhile(fn(string $line) => str_starts_with($line, '*/')===FALSE)
            ->takeWhile(fn(string $line) => str_starts_with($line, '/**')===FALSE)
            ->pipe(function (Collection $lines) {
                if ($lines->isEmpty()) {
                    return $lines;
                }
                return $lines->concat(['/**']);
            })
            ->values()
            ->map(fn(string $line) => Str::of($line)->replace('@param ?', '@param NULL|')->value())
            ->map(fn(string $line) => Str::of($line)->replace('@param ', '@param \CodeTooling\OmittedArg|')->value())
            ->reverse()
            ->values();

        $argumentForNewSelfInWithMethodLines = $constructorParametersForWithMethodLines
            ->map(fn(string $line) => Str::match('/\$\w+/', $line))
            ->map(fn(string $line) => Str::replaceStart('$', '', $line))
            ->map(function (string $parameterName) {
                return Str::of($parameterName)->replaceStart('$', '')->value();
            })
            ->map(fn(string $parameterName) => "$parameterName: \$$parameterName instanceof \CodeTooling\OmittedArg ? \$this->$parameterName : \$$parameterName");

        $stringWithMethods = <<<METHOD
            {$phpDocForWithMethodLines->implode("\n    ")}
            public function with({$constructorParametersForWithMethodLines->implode(", ")}): self
            {
                return new self({$argumentForNewSelfInWithMethodLines->implode(", ")});
            }
        METHOD;

        return $stringWithMethods;
    }

    public function identifyOldMethod(
        string $fileContents
    ): ?string {
        if (
            $candidateLinesForMethod = $constructorParametersForWithMethodLines = Str::of($fileContents)
                ->explode("\n")
                ->doesntContain(fn(string $line) => str_starts_with($line, "    public function with(")
                )
        ) {
            return NULL;
        }

        $candidateLinesForMethod = $constructorParametersForWithMethodLines = Str::of($fileContents)
            ->explode("\n")
            ->skipWhile(fn(string $line) => str_starts_with($line, "    public function with(")===FALSE)
            ->slice(NULL, 4)
            ->values();

        // Verify all line
        if (Str::of( $candidateLinesForMethod[0])->startsWith('    public function with(')===FALSE) {
            throw new \Exception();
        }
        if (Str::of( $candidateLinesForMethod[1])->startsWith('    {')===FALSE) {
            throw new \Exception();
        }
        if (Str::of( $candidateLinesForMethod[2])->startsWith('        return new self(')===FALSE) {
            throw new \Exception();
        }
        if (Str::of( $candidateLinesForMethod[3])->startsWith('    }')===FALSE) {
            throw new \Exception();
        }

        $methodWithoutPhpDoc = $candidateLinesForMethod->implode("\n");

        // Extract PHPDoc if exists
        $phpDoc = $phpDocForWithMethodLines = Str::of($fileContents)
            ->explode("\n")
            ->reverse()
            ->values()
            ->skipWhile(fn(string $line) => str_starts_with($line, '    public function with(')===FALSE)
            ->values()
            ->pipe(function (Collection $lines) {
                if (str_starts_with($lines[1], '    */')===FALSE) {
                    return collect();
                }
                return $lines;
            })
            ->skipWhile(fn(string $line) => str_starts_with($line, '    */')===FALSE)
            ->takeWhile(fn(string $line) => str_starts_with($line, '    /**')===FALSE)
            ->pipe(function (Collection $lines) {
                if ($lines->isEmpty()) {
                    return $lines;
                }
                return $lines->concat(['    /**']);
            })
            ->values()
            ->reverse()
            ->implode("\n");


        $string = trim(<<<STRING
        $phpDoc
        $methodWithoutPhpDoc

        STRING, "\n");

        return $string;
    }
}
