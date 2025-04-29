<?php


namespace App\Exports;

use App\Http\Controllers\CommonFunctions;
use App\Models\AmcMaster;
use App\Models\ServiceDetail;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\BeforeSheet;

class ServiceDetailsExport implements FromCollection, WithHeadings, WithCustomStartCell, WithEvents
{
    use CommonFunctions;
    /**
     * @return \Illuminate\Support\Collection
     */

    protected $startDate, $endDate, $exportChassisNumber, $exportVehicleNumber, $exportContactNumber, $exportVehicleType, $exportvehicleMasterId;

    public function __construct($startDate = '', $endDate = '', $exportChassisNumber = '', $exportVehicleNumber = '', $exportContactNumber = '', $exportVehicleType = '', $exportvehicleMasterId = '')
    {
        $this->startDate = $this->formatDateTime('Y-m-d', $startDate);
        $this->endDate   = $this->formatDateTime('Y-m-d', $endDate);
        $this->exportChassisNumber   = $exportChassisNumber;
        $this->exportVehicleNumber   = $exportVehicleNumber;
        $this->exportContactNumber   = $exportContactNumber;
        $this->exportVehicleType   = $exportVehicleType;
        $this->exportvehicleMasterId   = $exportvehicleMasterId;
    }
    public function collection()
    {
        $amcQuery = AmcMaster::query();
        $seriveQuery = ServiceDetail::query();
        if (! empty($this->startDate) && ! empty($this->endDate)) {
            $seriveQuery = $seriveQuery->whereBetween(DB::raw('DATE(service_details.service_date)'), [$this->startDate, $this->endDate]);
        }
        $amcIds = AmcMaster::query()
            ->when(!empty($this->exportChassisNumber), fn($q) => $q->where('chassis_number',  $this->exportChassisNumber))
            ->when(!empty($this->exportVehicleNumber), fn($q) => $q->where('vehicle_number', $this->exportVehicleNumber))
            ->when(!empty($this->exportContactNumber), fn($q) => $q->where('contact_number', $this->exportContactNumber))
            ->when(!empty($this->exportVehicleType), fn($q) => $q->where('vehicle_type', $this->exportVehicleType))
            ->when(!empty($this->exportvehicleMasterId), fn($q) => $q->where('vehicle_master_id', $this->exportvehicleMasterId))
            ->pluck('id')
            ->toArray();

        if (!empty($amcIds)) {
            $seriveQuery = $seriveQuery->whereIn('amc_id', $amcIds);
        }
        $results = $seriveQuery->get();

        $data     = [];
        foreach ($results as $row) {
            $data[] = [
                $row->amc_display_number,
                $row->vehicle_type_name,
                $row->display_amc_start_date,
                $row->display_amc_end_date,
                $row->chassis_number,
                $row->vehicle_number,
                $row->amc_package_type_name,
                $row->customer_name,
                $row->contact_number,
                $row->payment_type_name,
                $row->transaction_details,
                $row->amc_basic_price,
                $row->status_name,
            ];
        }

        return collect($data);
    }
    public function headings(): array
    {
        return [
            'Vehicle Type',
            'Chassis number',
            'Vehicle Number',
            'Contract Start Date ',
            'Contract End Date ',
            'Customer Name',
            'Mobile Number',
            'Service NO',
            'Service Date',
            'Service Remark',
            'Service Status',
            'Service Done by',
            'AMC Status',
        ];
    }

    public function startCell(): string
    {
        return 'A2';
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->setCellValue('A1', "Report Date: " . $this->startDate . " TO " . $this->endDate);
            },
        ];
    }
}
