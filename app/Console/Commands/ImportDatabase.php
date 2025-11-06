<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportDatabase extends Command
{
    protected $signature = 'db:import {file}';
    protected $description = 'Import an SQL file into the database';

    public function handle()
    {
        $file = $this->argument('file');

        if (!file_exists($file)) {
            $this->error("File not found: $file");
            return;
        }

        $this->info("Importing database from $file...");

        $command = sprintf(
            'mysql -u%s -p%s %s < %s',
            env('DB_USERNAME'),
            env('DB_PASSWORD'),
            env('DB_DATABASE'),
            $file
        );

        system($command);

        $this->info("Database import completed.");
    }
}
