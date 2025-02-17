<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\InquiryDetails;
use App\Models\Order;
use Carbon\Carbon;
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


        if ($request->inquiry_type == 'order') {
            $lastInquiryId = Order::orderBy('id', 'desc')->first()->id ?? 0;
            $data['created_by'] = 1;
            $data['customer_name'] = $request->name;
            $data['customer_mobile'] = $request->mobile;
            $data['customer_vehicle_no'] = strtoupper($request->vehicle_no);
            $data['order_name'] = strtoupper($request->order_name);
            $data['order_no'] = 'ORD-' . ($lastInquiryId + 1);
            $data['order_date'] = now()->format('Y-m-d H:i:s');
        } else {
            $data = $request->all();
            $data['created_by'] = 1;
            $data['vehicle_no'] = strtoupper($request->vehicle_no);
            $lastInquiryId = InquiryDetails::orderBy('id', 'desc')->first()->id ?? 0;
            $data['inquiry_no'] = 'INQ-' . ($lastInquiryId + 1);
        }


        // Save the inquiry
        if ($request->inquiry_type == 'order') {
            $inquirySave = Order::create($data);
            $inquiryId = $inquirySave->order_no;
        } else {
            $inquirySave = InquiryDetails::create($data);
            $inquiryId = $inquirySave->inquiry_no;
        }

        if ($this->isNotNullOrEmptyOrZero($inquirySave)) {

            if ($request->inquiry_type == 'order') {
                return $this->successResponse([], "Order No # $inquiryId added successfully. We will contact you soon.");
            } else {
                return $this->successResponse([], "Inquiry No # $inquiryId added successfully. We will contact you soon.");
            }
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

    public function checkInquiry(Request $request)
    {
        $today = Carbon::today()->toDateString();
        $mobileNumber = $request->input('mobile');
        $existingInquiry = InquiryDetails::where('mobile', $mobileNumber)
            ->whereRaw('DATE(created_at) = ?', [$today])
            ->first();
        if ($existingInquiry) {
            return $this->failResponse([], 'An inquiry has already been created with this mobile number today,Please try with another number. Thank you.');
        } else {
            return $this->successResponse([], "");
        }
    }
}
