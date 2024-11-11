<?php

namespace App\Exports;

use App\Http\Controllers\CommonFunctions;
use App\Models\InquiryDetails;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class InquiryDetailsExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */

    use CommonFunctions;

    protected $startDate, $endDate, $statusId;

    public function __construct($startDate = '', $endDate = '', $statusId = '')
    {
        $this->startDate = $this->formatDateTime('Y-m-d', $startDate);
        $this->endDate = $this->formatDateTime('Y-m-d', $endDate);
        $this->statusId = $statusId;
    }

    public function collection()
    {
        $query = InquiryDetails::query()->orderBy('confirm_date', 'desc')->orderBy('id', 'desc');

        if (!empty($this->startDate) && !empty($this->endDate)) {
            $query = $query->whereBetween(DB::raw('DATE(created_at)'), [$this->startDate, $this->endDate]);
        }

        if (!empty($this->statusId)) {
            $query = $query->where('status_id', $this->statusId);
        }

        $results = $query->get();

        $data = [];
        $serialNo = 1;
        foreach ($results as $row) {
            $data[] = [
                $serialNo++,
                $this->formatDateTime('d-m-Y', $row->created_at),
                $this->formatDateTime('d-m-Y H:i:m', $row->confirm_date),
                $row->inquiry_no,
                $row->name,
                $row->mobile,
                $row->vehicle_no,
                $this->getArrayNameById($this->serviceTypeArray, $row->service_type_id),
                $this->getArrayNameById($this->branchArray, $row->branch_id),
                $this->getArrayNameById($this->statusArray, $row->status_id),
                $row->status_remark,
                $this->formatDateTime('d-m-Y H:i:s', $row->created_at),
            ];
        }

        return collect($data);
    }

    public function headings(): array
    {
        return [
            'ID',
            'Inquiry Date',
            'Confirm Date',
            'Inquiry No',
            'Inquiry Name',
            'Mobile',
            'Vehicle Number',
            'Service Type',
            'Branch',
            'Status',
            'Status Remarks',
            'Created At',
        ];
    }
}
