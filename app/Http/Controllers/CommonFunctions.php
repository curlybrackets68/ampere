<?php

namespace App\Http\Controllers;

use App\Models\SystemLogs;

trait CommonFunctions
{

    // start fix constants for project
    protected $data = [];

    protected $serviceTypeArray = [
        "1" => 'PERIODIC MAINTENANCE',
        "2" => 'WARRANTY REPAIR',
        "3" => 'INSURANCE CLAIM',
        "4" => 'MINOR SERVICE',
        "5" => 'Other',
    ];
    protected $branchArray = [
        "1" => "KALALI",
        "2" => "SAMA",
    ];

    protected $statusArray = [
        "1" => 'Pending',
        "2" => 'Completed',
        "3" => 'Rejected',
        "4" => 'Confirmed',
    ];

    protected $actionLogsArray = [
        "1" => 'Add',
        "2" => 'Edit',
        "3" => 'change Status',
    ];
    // End fix constants for project

    public function convertNullOrEmptyStringToZero($str)
    {
        if (empty($str)) {
            return "0";
        } else {
            return strval($str);
        }
    }

    public function convertNullToEmptyString($str)
    {
        if ($this->isNullOrEmptyOrDateTimeZero($str)) {
            return "";
        } else {
            return strval($str);
        }
    }

    public function isNotNullOrEmptyOrZero($str)
    {
        if (!empty($str)) {
            return true;
        } else {
            return false;
        }
    }

    public function getArrayIdByName($arrayName, $value)
    {
        return array_search($value, $arrayName);
    }

    public function getArrayNameById($arrayName, $value)
    {
        return $arrayName[$value] ?? '';
    }

    public function isNullOrEmptyOrDateTimeZero($str)
    {
        if (
            empty($str)
            || ($str === '0000-00-00'
                || $str === '00-00-0000'
                || $str === '00:00:00'
                || $str === '0000-00-00 00:00:00'
                || $str === '00-00-0000 00:00:00'
            )
        ) {
            return true;
        } else {
            return false;
        }
    }

    public function formatDateTime($format = 'Y-m-d H:i:s', $mDateTime = '')
    {
        if ($this->isNullOrEmptyOrDateTimeZero($mDateTime)) {
            return "";
        } else {
            date_default_timezone_set("Asia/Kolkata");
            return date($format, strtotime($mDateTime));
        }
    }

    public function successResponse($data = [], $message = 'Success', $code = '1')
    {
        $returnArray = array(
            'code' => $code,
            'message' => $message,
        );
        if ($this->isNotNullOrEmptyOrZero($data)) {
            $returnArray = array_merge($returnArray, $data);
        }
        return response()->json($returnArray, 200);
    }

    public function failResponse($data = [], $message = 'Fail', $code = '0')
    {
        $returnArray = array(
            'code' => $code,
            'message' => $message,
        );
        if ($this->isNotNullOrEmptyOrZero($data)) {
            $returnArray = array_merge($returnArray, $data);
        }

        return response()->json($returnArray, 400);
    }

  
}
