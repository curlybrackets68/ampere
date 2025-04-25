<?php

namespace App\Jobs;

use App\Exports\LeadsExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class ExportLeadsToExcel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $params;

    public function __construct(array $params)
    {
        $this->params = $params;
    }

    public function handle()
    {
        $timestamp = now()->format('Ymd_His');
        $fileName = "exports/leads_{$timestamp}.xlsx";

        Excel::store(
            new LeadsExport(
                $this->params['startDate'],
                $this->params['endDate'],
                $this->params['salesmanId'],
                $this->params['leadSourceId'],
                $this->params['mobileNumber'],
                $this->params['customerName']
            ),
            $fileName,
            'local' // stores to storage/app/exports
        );
    }
}

