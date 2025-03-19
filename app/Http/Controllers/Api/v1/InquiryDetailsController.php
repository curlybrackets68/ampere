<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\InquiryDetails;
use App\Models\Order;
use App\Models\SystemLogs;
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

        // if ($validator->fails()) {
        //     return $this->failResponse([], 'Validation Error Please enter valid Vehicle Number,Mobile number', $validator->errors());
        // }

        $latestNumber = 0;
        if ($request->action_type == '2') {
            $lastOrderId = Order::orderBy('id', 'desc')->first()->id ?? 0;
            $data['created_by'] = 1;
            $data['customer_name'] = $request->name;
            $data['branch_id'] = $request->order_branch;
            $data['customer_vehicle_no'] = strtoupper($request->vehicle_no);
            $data['order_name'] = $request->order_details;
            $data['customer_mobile'] = $request->mobile;
            $data['order_no'] = 'ORD-' . ($lastOrderId + 1);
            $data['order_date'] = now()->format('Y-m-d H:i:s');
        } else {
            $data = $request->all();
            $data['created_by'] = 1;
            $data['vehicle_no'] = strtoupper($request->vehicle_no);
            $lastInquiryId = InquiryDetails::orderBy('id', 'desc')->first()->id ?? 0;
            $data['inquiry_no'] = 'INQ-' . ($lastInquiryId + 1);
        }


        // Save the inquiry
        if ($request->action_type == '2') {
            $orderSave = Order::create($data);
            $latestNumber = $orderSave->order_no;
            SystemLogs::create([
                'inquiry_id' => 0,
                'type' => '2', // for Order
                'type_id' => $orderSave->id, // for Order
                'remark'     => 'Part Order Created # '.$latestNumber,
                'action_id'  => 1,
                'created_by' => 1,
            ]);
        } else {
            $inquirySave = InquiryDetails::create($data);
            $latestNumber = $inquirySave->inquiry_no;
            SystemLogs::create([
                'inquiry_id' => $inquirySave->id,
                'type' => '1', // for Order
                'type_id' => $inquirySave->id, // for Order
                'remark'     => 'Inquiry Created # '.$latestNumber,
                'action_id'  => 1,
                'created_by' => 1,
            ]);

        }

        if ($this->isNotNullOrEmptyOrZero($latestNumber)) {

            if ($request->action_type == '2') {
                return $this->successResponse([], "Order No # $latestNumber added successfully. We will contact you soon.");
            } else {
                return $this->successResponse([], "Inquiry No # $latestNumber added successfully. We will contact you soon.");
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
