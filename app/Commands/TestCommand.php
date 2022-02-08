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
        $this->codeServerPassword = 'SympInterview@'.rand(1000, 9999);
        $this->line($this->getApplication()->getName());
        $this->info('Starting code-server');
        $command = 'export PASSWORD='.$this->codeServerPassword.';';
        $command .= 'code-server';
        $command .= ' /var/www/html';
        $command .= ' --auth=password';
        $command .= ' --cert=/home/ubuntu/certs/fullchain.pem'; // TODO: This can't be hardcoded
        $command .= ' --cert-key=/home/ubuntu/certs/privkey.pem'; // TODO: This can't be hardcoded

        /*$process = new Process([$command]);
        $process->run();
        foreach ($process as $type => $data) {
            if ($process::OUT === $type) {
                echo "\nRead from stdout: ".$data;
            } else { // $process::ERR === $type
                echo "\nRead from stderr: ".$data;
            }
        }*/
        $process = new BackgroundProcess($command);
        $process->run();
        $this->info('Started with PID ' . $process->getPid());
    }

}
