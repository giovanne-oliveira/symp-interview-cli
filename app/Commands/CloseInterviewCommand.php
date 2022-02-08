<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CloseInterviewCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'interview:close {name : The candidate name (required)}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Close an interview instance for a candidate';

    private $candidateName;

    private $removeCandidateData = false;

    private $backupCandidateFiles = true;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->line($this->getApplication()->getName());

        $candidateName = $this->argument('name');
        $candidateName = str_replace(' ', '_', $candidateName);

        $this->backupCandidateFiles = $this->confirm('Do you want to backup the candidate files and database?', true);
        $this->removeCandidateData = $this->confirm('Do you want to delete the candidate files and database?', true);


        $this->info('Closing interview environment for ' . $candidateName);
        $this->candidateName = $candidateName;

        if($this->backupCandidateFiles) {
            if ($this->task('Dumping candidate\'s database...', function () {
                
            }) === false) {
                $this->displayError('Error while backing up candidate\'s database');
            }

            if ($this->task('Packing candidate\'s files...', function () {
                
            }) === false) {
                $this->displayError('Error while backing up candidate\'s files');
            }
        }

        if($this->removeCandidateData) {
            if ($this->task('Dropping candidate\'s database...', function () {
                
            }) === false) {
                $this->displayError('Error while removing candidate\'s database');
            }

            if ($this->task('Removing candidate\'s database user...', function () {
                
            }) === false) {
                $this->displayError('Error while removing candidate\'s database');
            }

            if ($this->task('Removing candidate\'s files...', function () {
                
            }) === false) {
                $this->displayError('Error while removing candidate\'s files');
            }
        }

    }
}
