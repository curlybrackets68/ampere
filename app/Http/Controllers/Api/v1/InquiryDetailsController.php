<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\InquiryDetails;
use Illuminate\Http\Request;

class InquiryDetailsController extends Controller
{
    public function addInquiry(Request $request)
    {

        $data = $request->all();
        $data['created_by'] = 1;
        $lastInquiryId = InquiryDetails::orderBy('id', 'desc')->first()->id ?? 0;
        $data['inquiry_no'] = 'INQ-' . $lastInquiryId + 1; // If no records, start with 1

        $data['inquiry_date'] = $this->formatDateTime('Y-m-d', $request->input('inquiry_date'));
        $data['service_date'] = $this->formatDateTime('Y-m-d', $request->input('service_date'));
        $inquirySave = InquiryDetails::create($data);
        if ($this->isNotNullOrEmptyOrZero($inquirySave)) {

            $inquiryId = $inquirySave->inquiry_no;
            return $this->successResponse(['id' => $this->convertNullOrEmptyStringToZero($inquiryId)], 'Inquiry Added Successfully.');
        } else {
            return $this->failResponse([], 'Something Wrong');
        }
    }
}