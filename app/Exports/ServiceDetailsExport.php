<?php


namespace App\Exports;

use App\Http\Controllers\CommonFunctions;
use App\Models\AmcMaster;
use App\Models\AmcPackageMaster;
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

    protected $startDate, $endDate, $exportChassisNumber, $exportVehicleNumber, $exportContactNumber, $exportVehicleType, $exportvehicleMasterId, $exportStatusId, $exportActionType;

    public function __construct($startDate = '', $endDate = '', $exportChassisNumber = '', $exportVehicleNumber = '', $exportContactNumber = '', $exportVehicleType = '', $exportvehicleMasterId = '', $exportStatusId = '',$exportActionType='')
    {
        $this->startDate = $this->formatDateTime('Y-m-d', $startDate);
        $this->endDate   = $this->formatDateTime('Y-m-d', $endDate);
        $this->exportChassisNumber   = $exportChassisNumber;
        $this->exportVehicleNumber   = $exportVehicleNumber;
        $this->exportContactNumber   = $exportContactNumber;
        $this->exportVehicleType   = $exportVehicleType;
        $this->exportvehicleMasterId   = $exportvehicleMasterId;
        $this->exportStatusId   = $exportStatusId;
        $this->exportActionType   = $exportActionType;
    }
    public function collection()
    {
        $serviceList = ServiceDetail::query();
        if (isset($this->exportActionType) && !empty($this->exportActionType)) {
            $amcIds = AmcMaster::query()
                ->when(!empty($this->exportChassisNumber), fn($q) => $q->where('chassis_number', $this->exportChassisNumber))
                ->when(!empty($this->exportVehicleNumber), fn($q) => $q->where('vehicle_number', $this->exportVehicleNumber))
                ->when(!empty($this->exportContactNumber), fn($q) => $q->where('contact_number', $this->exportContactNumber))
                ->when(!empty($this->exportVehicleType), fn($q) => $q->where('vehicle_type', $this->exportVehicleType))
                ->when(!empty($this->exportvehicleMasterId), fn($q) => $q->where('vehicle_master_id', $this->exportvehicleMasterId))
                ->pluck('id')
                ->toArray();
            if (!empty($amcIds)) {
                $serviceList = $serviceList->whereIn('amc_id', $amcIds);
            }
            if (!empty($this->status_id)) {
                $serviceList = $serviceList->where('status', $this->exportStatusId);
            }
            if (! empty($this->startDate) && ! empty($this->endDate)) {
                $serviceList = $serviceList->whereBetween(DB::raw('DATE(service_details.service_date)'), [$this->startDate, $this->endDate]);
            }
        } else {
            $serviceList = $serviceList->where('status', 1);
            if (! empty($this->startDate) && ! empty($this->endDate)) {
                $serviceList = $serviceList->whereBetween(DB::raw('DATE(service_details.service_date)'), [$this->startDate, $this->endDate]);
            }
            $serviceList = $serviceList->orwhere(DB::raw('DATE(service_details.service_date)'), '<', $this->startDate);
        }
        $results = $serviceList->get();
      
        $data     = [];
        foreach ($results as $row) {
            $data[] = [
                $row->amc_master_details->vehicle_type_name,
                $row->amc_master_details->chassis_number,
                $row->amc_master_details->vehicle_number,
                $row->amc_master_details->display_amc_start_date,
                $row->amc_master_details->display_amc_end_date,
                $row->amc_master_details->customer_name,
                $row->amc_master_details->contact_number,
                $row->service_no,
                $row->display_service_date,
                $row->service_remark,
                $row->status_name,
                $row->service_by,
                $row->amc_master_details->status_name,
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

                $sheet->setCellValue('A1', "Report Date: " . $this->formatDateTime('d-M-Y', $this->startDate) . " TO " . $this->formatDateTime('d-M-Y', $this->endDate));

                $sheet->mergeCells('A1:M1');

                $sheet->getStyle('A1')->getFont()->setBold(true);

                $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
            },
        ];
    }
}
