<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
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

        $mysqldumpPath = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';
        $command = "\"{$mysqldumpPath}\" --user={$user} --password=\"{$pass}\" --host={$host} {$db} > \"{$backupPath}\"";
        exec($command, $output, $result);


        if ($result !== 0) {
            $this->error("Backup failed!");
            return;
        }

        Mail::raw("Daily database backup attached.", function ($message) use ($backupPath, $fileName) {
            $message->to(['pinank1510@gmail.com','mihirpatel19.mp@gamil.com'])   // 👈 multiple TO
                ->cc(['curlybrackets68@gmail.com'])       // 👈 multiple CC
                ->subject('Daily DB Backup of Ampere '. Carbon::now()->format('d-m-Y'))
                ->attach($backupPath, [
                    'as' => $fileName,
                    'mime' => 'application/sql',
                ]);
        });

        $this->info("Backup successful & emailed!");
    }
}
