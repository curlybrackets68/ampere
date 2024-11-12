<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\InquiryDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;

class InquiryDetailsController extends Controller
{
    public function addInquiry(Request $request)
    {
        // Define validation rules
        $rules = [
            'vehicle_no' => 'required', // Adjust size based on vehicle number length
            'mobile' => 'required|digits:10' // 10-digit validation for mobile
        ];

        // Define custom error messages
        $messages = [
            'vehicle_no.required' => 'Vehicle number is required.',
            'mobile.required' => 'Mobile number is required.',
            'mobile.digits' => 'Mobile number must be 10 digits long.'
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return $this->failResponse([], 'Validation Error Please enter valid Vehicle Number,Mobile number', $validator->errors());
        }

        $data = $request->all();
        $data['created_by'] = 1;
        $data['vehicle_no'] = strtoupper($request->vehicle_no);
        $lastInquiryId = InquiryDetails::orderBy('id', 'desc')->first()->id ?? 0;
        $data['inquiry_no'] = 'INQ-' . ($lastInquiryId + 1);

        // Save the inquiry
        $inquirySave = InquiryDetails::create($data);
        if ($this->isNotNullOrEmptyOrZero($inquirySave)) {
            $inquiryId = $inquirySave->inquiry_no;
            return $this->successResponse([], "Inquiry No # $inquiryId added successfully. We will contact you soon.");
        } else {
            return $this->failResponse([], 'Something went wrong.');
        }
    }

    public function runArtisan(Request $request)
    {
        $command = $request->command;
        Artisan::call($command);
        return;
    }
}
