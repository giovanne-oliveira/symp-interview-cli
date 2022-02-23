<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Cocur\BackgroundProcess\BackgroundProcess;

class TestCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'testCode {name : The candidate name (required)}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Create a new interview instance for a candidate';

    private $candidateName;
    private $codeServerPassword;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $destination = env('PUBLIC_HTML_PATH', '/var/www/html') . '/' . $this->candidateName;

        var_dump(File::copyDirectory(storage_path('candidateFolderStub'), $destination, true));
    }

}
