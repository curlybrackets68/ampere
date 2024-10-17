<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\InquiryDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class InquiryDetailsController extends Controller
{
    public function addInquiry(Request $request)
    {

        $data = $request->all();
        $data['created_by'] = 1;
        $data['vehicle_no']= strtoupper($request->vehicle_no);
        $lastInquiryId = InquiryDetails::orderBy('id', 'desc')->first()->id ?? 0;
        $data['inquiry_no'] = 'INQ-' . $lastInquiryId + 1; // If no records, start with 1

        $inquirySave = InquiryDetails::create($data);
        if ($this->isNotNullOrEmptyOrZero($inquirySave)) {

            $inquiryId = $inquirySave->inquiry_no;
            return $this->successResponse([], "Inquiry No # $inquiryId added  Successfully, We will contact you soon.");
        } else {
            return $this->failResponse([], 'Something Wrong');
        }
    }

    public function runArtisan(Request $request){
        $command = $request->command;
        Artisan::call($command);
        return;
    }
}
