<?php
namespace App\Exports;

use App\Http\Controllers\CommonFunctions;
use App\Models\Lead;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LeadsExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */

    use CommonFunctions;

    protected $startDate, $endDate;

    public function __construct($startDate = '', $endDate = '')
    {
        $this->startDate = $this->formatDateTime('Y-m-d', $startDate);
        $this->endDate   = $this->formatDateTime('Y-m-d', $endDate);
    }

    public function collection()
    {
        $query = Lead::query()->select('leads.*', 'vehicle.name AS vehicleName', 'salesman.name AS salesmanName', 'lead_sources.name AS leadSourceName')
            ->leftJoin('vehicle', 'vehicle.id', '=', 'leads.vehicle')
            ->leftJoin('lead_sources', 'lead_sources.id', '=', 'leads.lead_source')
            ->leftJoin('salesman', 'salesman.id', '=', 'leads.salesman');

        if (! empty($this->startDate) && ! empty($this->endDate)) {
            $query = $query->whereBetween(DB::raw('DATE(leads.created_at)'), [$this->startDate, $this->endDate]);
        }

        $results = $query->get();

        $data     = [];
        $serialNo = 1;
        foreach ($results as $row) {
            $data[] = [
                $serialNo++,
                $this->formatDateTime('d-m-Y', $row->created_at),
                $row->name,
                $row->vehicleName,
                $row->mobile,
                $row->leadSourceName,
                $row->salesmanName
            ];
        }

        return collect($data);
    }

    public function headings(): array
    {
        return [
            'ID',
            'Lead Date',
            'Name',
            'Vehicle Name',
            'Mobile',
            'Lead Source Name',
            'Salesman Name'
        ];
    }
}
