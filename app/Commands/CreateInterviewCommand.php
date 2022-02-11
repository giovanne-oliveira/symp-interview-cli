<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Origin\Filesystem\Folder as OriginDirFS;
use Cocur\BackgroundProcess\BackgroundProcess;

class CreateInterviewCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'init {name : The candidate name (required)}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Create a new interview instance for a candidate';

    private $runtimeErrorMessage;
    private $candidateName;
    private $databaseInfo = [
        'host' => 'localhost',
        'database' => '',
        'username' => '',
        'password' => '',
        'port' => '3306',
    ];

    private $codeServerPassword;
    private $codeServerHost = 'interview.giovanne.dev';
    private $codeServerPort = 8090;

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
        $this->info('Creating interview environment for ' . $candidateName);
        $this->candidateName = $candidateName;

        if ($this->task('Checking if interview workspace is writeable...', function () {
            if (!is_writeable('/var/www/interviewWorkspace')) {
                return false;
            }
        }, 'checking') === false) {
            $this->displayError('The /var/www/interviewWorkspace is not writeable.');
        }

        if ($this->task('Checking if the name is not already used', function () {
            return $this->checkCandidateDirExists();
        }) == false) {
            $this->displayError('There\'s another candidate with the same name with an ongoing interview.');
        }

        if($this->task('Creating directory...', function () {
            return $this->createCandidateDirectory();
        }) === false){
            $this->displayError('There was an error creating the candidate directory.');
        }

        if($this->task('Creating database...', function () {
            return $this->createCandidateDatabase();
        }) === false){
            $this->displayError('There was an error creating the candidate database.');
        }

        if($this->task('Creating database user...', function () {
            return $this->createCandidateDatabaseUser();
        }) === false){
            $this->displayError('There was an error creating the candidate database credentials.');
        }

        if($this->task('Populating test folder with Skeleton files...', function () {
            return $this->populateCandidateFolder();
        }) === false){
            $this->displayError('There was an error populating the candidate folder.');
        }

        if($this->task('Fixing directory\'s permission...', function () {
            return $this->fixCandidateDirectoryPermissions();
        }) === false){
            $this->displayError('There was an error fixing the candidate directory\'s permissions.');
        }

        if($this->task('Initializing Visual Studio Code Server...', function () {
            return $this->launchCodeServerInstance();
        }) === false){
            $this->displayError('There was an error fixing the candidate directory\'s permissions.');
        }

        $this->info('Environment created successfully. All the information the candidate will need is in a file called INSTRUCTIONS.MD into the directory.');
        $this->newLine(2);
        $this->line('Everything is set to go. Your candidate must open the browser and paste the Live Coding URL provided below.');
        $this->line('Then, the candidate must type the password also provided below, to have access to the Live Coding environment.');
        $this->line('Once with vscode open in the browser, the candidate will find a INSTRUCTIONS.md file in the root of the project.');
        $this->line('This file contains all the information needed to run the interview, including database credentials, Base URL and PHPMyAdmin URL.');
        $this->newLine(2);
        $this->info("Live Coding URL: https://".$this->codeServerHost.":".$this->codeServerPort);
        $this->info("Password: ".$this->codeServerPassword);
    }

    private function createCandidateDirectory()
    {
        return Storage::disk('public_html')->makeDirectory($this->candidateName);
        //return true; // DEBUG
    }

    private function fixCandidateDirectoryPermissions()
    {
        // TODO: Fix the ownership group
        $chownResponse = OriginDirFS::chgrp('/var/www/html/' . $this->candidateName, 'www-data'); // TODO: Hardcoded path
        $chmodResponse = OriginDirFS::chmod('/var/www/html/' . $this->candidateName, 0755); // TODO: Hardcoded path
        if($chownResponse && $chmodResponse){
            return true;
        }else{
            return false;
        }
    }

    private function checkCandidateDirExists()
    {
        $dirs = Storage::disk('public_html')->directories();
        if(in_array($this->candidateName, $dirs)){
            return false;
        }else{
            return true;
        }
        //return true; // DEBUG
    }

    private function displayError($errorMessage, $halt = true)
    {
        $this->newLine(2);
        $this->error($errorMessage);
        $this->newLine(2);
        if ($halt == true) {
            die();
        }
    }

    private function createCandidateDatabase()
    {
        // Check if the database exists
        $dbName = $this->candidateName . '_interview';
        $this->databaseInfo['database'] = $dbName;
        $dbExists = DB::connection('mysql')->select('SHOW DATABASES LIKE "?"', [$dbName]);
        if (count($dbExists) > 0) {
            return false;
            //return true; // DEBUG
        }
        $response = DB::connection('mysql')->statement("CREATE DATABASE IF NOT EXISTS $dbName");
        return $response;
        //return true; // DEBUG
    }

    private function createCandidateDatabaseUser()
    {
        // Check if the database user exists
        $dbUser = $this->candidateName . '_interview';
        $dbPassword = 'SympTest@123';
        $dbName = $this->databaseInfo['database'];

        $this->databaseInfo['username'] = $dbUser;
        $this->databaseInfo['password'] = $dbPassword;

        $dbUserExists = DB::connection('mysql')->select('SELECT * FROM mysql.user WHERE user = ?', [$dbUser]);
        if (count($dbUserExists) > 0) {
            return false;
            //return true; // DEBUG
        }
        $createUserQuery = DB::connection('mysql')->statement("CREATE USER '$dbUser'@'localhost' IDENTIFIED BY '$dbUser'");
        $grantUserQuery = DB::connection('mysql')->statement("GRANT ALL PRIVILEGES ON $dbName.* TO '$dbUser'@'localhost'");
        $setPasswordQuery = DB::connection('mysql')->statement("ALTER USER '$dbUser'@'localhost' IDENTIFIED BY '$dbPassword'");
    

        if($createUserQuery && $grantUserQuery && $setPasswordQuery){
            return true;
        }else{
            return false;
        }
    }

    private function populateCandidateFolder()
    {
        $destination = '/var/www/html/' . $this->candidateName;

        $copyResponse = File::copyDirectory('storage/candidateFolderStub', $destination, true);

        // Replace the database info in the instruction file
        $instructionStub = $destination . '/INSTRUCTIONS.md';
        $content = File::get($instructionStub);
        $search = [
            '{{dbHost}}',
            '{{dbUsername}}',
            '{{dbPassword}}',
            '{{dbName}}',
            '{{dbPort}}',
            '{{pmaUrl}}',
            '{{baseUrl}}',
        ];
        $replace = [
            $this->databaseInfo['host'],
            $this->databaseInfo['username'],
            $this->databaseInfo['password'],
            $this->databaseInfo['database'],
            $this->databaseInfo['port'],
            'https://interview.giovanne.dev/phpmyadmin/', // TODO: Hardcoded URL
            'https://interview.giovanne.dev/'.$this->candidateName,
        ];

        $writeFileResponse = File::put(
            $instructionStub,
            Str::replace(
                $search,
                $replace,
                $content
            )
        );

        if($copyResponse && $writeFileResponse){
            return true;
        }else{
            return false;
        }
    }

    private function launchCodeServerInstance()
    {
        $this->codeServerPassword = 'SympInterview@'.rand(1000, 9999);
        $command = 'export PASSWORD='.$this->codeServerPassword.';';
        $command .= 'code-server';
        $command .= ' /var/www/html/'.$this->candidateName;
        $command .= ' --auth=password';
        $command .= ' --cert=/home/ubuntu/certs/fullchain.pem'; // TODO: This can't be hardcoded
        $command .= ' --cert-key=/home/ubuntu/certs/privkey.pem'; // TODO: This can't be hardcoded

        $process = new BackgroundProcess($command);
        $process->run();
        $pid = $process->getPid();
        if($process->isRunning()){
            return DB::insert('INSERT INTO code_server_instances (candidate_name, pid, password, status, started_at) VALUES (?, ?, ?, 1, NOW())', [$this->candidateName, $pid, $this->codeServerPassword]);
        }else{
            return false;
        }
    }
}
