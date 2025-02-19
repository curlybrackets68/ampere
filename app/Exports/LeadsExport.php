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

    protected $startDate, $endDate, $salesmanId, $leadSourceId, $mobileNumber, $customerName;

    public function __construct($startDate = '', $endDate = '', $salesmanId = '', $leadSourceId = '', $mobileNumber = '', $customerName = '')
    {
        $this->startDate = $this->formatDateTime('Y-m-d', $startDate);
        $this->endDate   = $this->formatDateTime('Y-m-d', $endDate);
        $this->salesmanId = $salesmanId;
        $this->leadSourceId = $leadSourceId;
        $this->mobileNumber = $mobileNumber;
        $this->customerName = $customerName;
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

        if (! empty($this->salesmanId)) {
            $query = $query->where('salesman.id', $this->salesmanId);
        }

        if (! empty($this->leadSourceId)) {
            $query = $query->where('lead_sources.id', $this->leadSourceId);
        }

        if (! empty($this->mobileNumber)) {
            $query = $query->where('leads.mobile', 'like', '%' . $this->mobileNumber . '%');
        }

        if (! empty($this->customerName)) {
            $query = $query->where('leads.name', 'like', '%' . $this->customerName . '%');
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
