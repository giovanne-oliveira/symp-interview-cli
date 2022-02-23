<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Origin\Filesystem\Folder as OriginDirFS;
use Cocur\BackgroundProcess\BackgroundProcess;
use App\Libraries\Lock;

class CheckEnvConfigsCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'housekeeping:check-env-configs';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Print the application\'s environment variables';

    private $runtimeErrorMessage;


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Setup variables

        // Run the command
        $this->line($this->getApplication()->getName());

        $this->info('Fetching environment variables...');

        $this->table([
            'Variable',
            'Value'
        ], [
            ['DB_WORKER_HOST', env('DB_WORKER_HOST')],
            ['DB_WORKER_PORT', env('DB_WORKER_PORT')],
            ['DB_WORKER_SCHEMA', env('DB_WORKER_SCHEMA')],
            ['DB_WORKER_USER', env('DB_WORKER_USER')],
            ['DB_WORKER_PASS', env('DB_WORKER_PASS')],
            ['PUBLIC_URL', env('PUBLIC_URL')],
            ['PUBLIC_HTML_PATH', env('PUBLIC_HTML_PATH')],
            ['PUBLIC_HTML_GROUP', env('PUBLIC_HTML_GROUP')],
            ['PUBLIC_HTML_OWNER', env('PUBLIC_HTML_OWNER')],
            ['CANDIDATE_FOLDER_PERMISSION', env('CANDIDATE_FOLDER_PERMISSION')],
            ['CANDIDATE_DEFAULT_MYSQL_PASSWORD', env('CANDIDATE_DEFAULT_MYSQL_PASSWORD')],
            ['PHPMYADMIN_URL', env('PHPMYADMIN_URL')],
            ['CODE_SERVER_HOST', env('CODE_SERVER_HOST')],
            ['CODE_SERVER_PORT', env('CODE_SERVER_PORT')],
            ['CODE_SERVER_ENABLE_SSL', env('CODE_SERVER_ENABLE_SSL')],
            ['CODE_SERVER_SSL_CERT_PATH', env('CODE_SERVER_SSL_CERT_PATH')],
            ['CODE_SERVER_SSL_KEY_PATH', env('CODE_SERVER_SSL_KEY_PATH')],
            ['MYSQLDUMP_PATH', env('MYSQLDUMP_PATH')],
        ]);

    }


}
