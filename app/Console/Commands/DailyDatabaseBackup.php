<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DailyDatabaseBackup extends Command
{
    protected $signature = 'backup:daily-db';
    protected $description = 'Backup MySQL database and email the SQL file';

    public function handle()
    {
        $db = config('database.connections.mysql.database');
        $user = config('database.connections.mysql.username');
        $pass = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');

        $fileName = 'backup_' . Carbon::now()->format('Y_m_d_His') . '.sql';
        $backupPath = storage_path('app/backups/' . $fileName);

        if (!File::exists(storage_path('app/backups'))) {
            File::makeDirectory(storage_path('app/backups'), 0755, true);
        }

        // $mySqlDumpPath = 'C:\\xampp\\mysql\\bin\\mysqldump.exe'; // Change this on server if needed
        $mySqlDumpPath = '/usr/bin/mysqldump'; // path for LIVE
        $command = "\"{$mySqlDumpPath}\" --user={$user} --password=\"{$pass}\" --host={$host} {$db} > \"{$backupPath}\"";

        exec($command, $output, $result);


        if ($result !== 0) {
            Log::error("DB Backup failed at " . now()->toDateTimeString());
            Log::error("Command: " . $command);
            Log::error("Output: " . implode("\n", $output));
            $this->error("Backup failed!");
            return;
        }

        Log::info(" Backup created: " . $fileName);

        try {
            Mail::raw("Daily DB Backup of Ampere of Date:- " . Carbon::now()->format('d-m-Y') . " Attached", function ($message) use ($backupPath, $fileName) {
                $message->to(['automotivechirag@gmail.com'])
                    ->cc(['curlybrackets68@gmail.com'])
                    ->subject('Daily DB Backup of Ampere ' . Carbon::now()->format('d-m-Y'))
                    ->attach($backupPath, [
                        'as' => $fileName,
                        'mime' => 'application/sql',
                    ]);
            });

            Log::info("DB Backup emailed successfully at " . now()->toDateTimeString());
        } catch (\Exception $e) {
            Log::error("Mail sending failed: " . $e->getMessage());
        }

        File::delete($backupPath);
        Log::info("Backup file deleted after emailing: " . $fileName);

        $this->info("Backup successful & emailed!");
    }
}
