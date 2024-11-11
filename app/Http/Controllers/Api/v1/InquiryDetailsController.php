<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\InquiryDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
<<<<<<< HEAD
=======
use Illuminate\Support\Facades\Validator;
>>>>>>> development

class InquiryDetailsController extends Controller
{
    public function addInquiry12(Request $request)
    {

        $data = $request->all();
        $data['created_by'] = 1;
        $data['vehicle_no'] = strtoupper($request->vehicle_no);
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

    public function addInquiry(Request $request)
    {
        // Define validation rules
        $rules = [
            'vehicle_no' => 'required|alpha_num|size:10', // Adjust size based on vehicle number length
            'mobile' => 'required|digits:10' // 10-digit validation for mobile
        ];

        // Define custom error messages
        $messages = [
            'vehicle_no.required' => 'Vehicle number is required.',
            'vehicle_no.alpha_num' => 'Vehicle number must be alphanumeric.',
            'vehicle_no.size' => 'Vehicle number must be exactly 10 characters.', // Adjust based on actual format
            'mobile.required' => 'Mobile number is required.',
            'mobile.digits' => 'Mobile number must be 10 digits long.'
        ];

        // Perform validation
        $validator = Validator::make($request->all(), $rules, $messages);

        // Check if validation fails
        if ($validator->fails()) {
            // Return custom fail response with validation errors
            return $this->failResponse([], 'Validation Error Please enter valid Vehicle Number,Mobile number', $validator->errors());
        }

        // Process data if validation passes
        $data = $request->all();
        $data['created_by'] = 1;
        $data['vehicle_no'] = strtoupper($request->vehicle_no);

        // Generate inquiry number
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
