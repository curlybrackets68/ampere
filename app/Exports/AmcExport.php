<?php

namespace App\Exports;

use App\Http\Controllers\CommonFunctions;
use App\Models\AmcMaster;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\BeforeSheet;

class AmcExport implements FromCollection, WithHeadings, WithCustomStartCell, WithEvents
{
    use CommonFunctions;
    /**
     * @return \Illuminate\Support\Collection
     */

    protected $startDate, $endDate, $exportChassisNumber, $exportVehicleNumber, $exportContactNumber, $exportVehicleType, $exportVehicleMasterId, $exportAmcStatusId;

    public function __construct($startDate = '', $endDate = '', $exportChassisNumber = '', $exportVehicleNumber = '', $exportContactNumber = '', $exportVehicleType = '', $exportVehicleMasterId = '', $exportAmcStatusId = '')
    {
        $this->startDate = $this->formatDateTime('Y-m-d', $startDate);
        $this->endDate   = $this->formatDateTime('Y-m-d', $endDate);
        $this->exportChassisNumber   = $exportChassisNumber;
        $this->exportVehicleNumber   = $exportVehicleNumber;
        $this->exportContactNumber   = $exportContactNumber;
        $this->exportVehicleType   = $exportVehicleType;
        $this->exportVehicleMasterId   = $exportVehicleMasterId;
        $this->exportAmcStatusId   = $exportAmcStatusId;
    }
    public function collection()
    {
        $query = AmcMaster::query();
        if (! empty($this->startDate) && ! empty($this->endDate)) {
            $query = $query->whereBetween(DB::raw('DATE(amc_masters.amc_end_date)'), [$this->startDate, $this->endDate]);
        }
        if (!empty($this->exportChassisNumber)) {
            $query = $query->where('chassis_number', $this->exportChassisNumber);
        }
        if (!empty($this->exportVehicleNumber)) {
            $query = $query->where('vehicle_number', $this->exportVehicleNumber);
        }
        if (!empty($this->exportContactNumber)) {
            $query = $query->where('contact_number', $this->exportContactNumber);
        }
        if (!empty($this->exportVehicleType)) {
            $query = $query->where('vehicle_type', $this->exportVehicleType);
        }
        if (!empty($this->exportVehicleMasterId)) {
            $query = $query->where('vehicle_master_id', $this->exportVehicleMasterId);
        }
        if (!empty($this->exportAmcStatusId)) {
            $query = $query->where('status', $this->exportAmcStatusId);
        }

        $results = $query->get();

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
            'Contract ID',
            'Vehicle Type',
            'Contract Start Date',
            'Contract End Date',
            'Chassis Number',
            'Vehicle Number',
            'Service Package',
            'Customer Name',
            'Mobile Number',
            'Payment Type',
            'Transaction ID',
            'AMC Amount',
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
