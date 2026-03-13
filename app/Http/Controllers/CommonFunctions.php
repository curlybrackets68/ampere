<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\UserRight;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Str;

trait CommonFunctions
{

    // start fix constants for project
    protected $data = [];

    protected $WHATSAPP_LICENSE_NUMBER = '94601679459';

    // protected $WHATSAPP_API_KEY = '3CvztP4HIDFU5hOKjEfTV9Jaw';
    protected $WHATSAPP_API_KEY = 'k3TVNMXdcgrs19mPW0xKhRUBa';

    protected $WHATSAPP_URL = 'https://app.ampala.in/api/sendtemplate.php';

    protected $serviceTypeArray = [
        "1" => 'Regular service',
        "2" => 'Vehicle Off Road',
        "3" => 'Insurance Repair',
        //"4" => 'MINOR SERVICE',
        //  "5" => 'Other',
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
        "5" => 'In Workshop',
        "6" => 'Ordered',
        "7" => 'Received',
        "8" => 'Cancelled',
        "9" => 'Fitment',
        "10" => 'Active',
        "11" => 'Deactive',
        "12" => 'New',
        "13" => 'Renew',
    ];

    protected $actionLogsArray = [
        "1" => 'Add',
        "2" => 'Edit',
        "3" => 'Change Status',
        "4" => 'Login',
        "5" => 'Logout',
        "6" => 'Renew',
    ];

    protected $leadSource = [
        '1' => 'BTL Field Activity',
        '2' => 'Existing Customer',
        '3' => 'Natural Walk-In',
        '4' => 'Reference',
    ];

    protected $vehicleTypeArray = [
        '1' => 'New',
        '2' => 'Old',
    ];

    protected $paymentTypeArray = [
        '1' => 'Cash',
        '2' => 'Online',
        '3' => 'Cheque',
    ];

    protected $amcServiceType = [
        '1' => 'Free',
        '2' => 'Paid',
    ];

    // reminder Days Array
    protected $reminderDays = [
        '5' => [
            // TVS King Duramax Plus
            11,
            33,
            55,
            76,
            98,
            120,
            143,
            165,
            186,
            210,
            231,
        ],
        '4' => [
            // TVS King EV Max
            13,
            28,
            58,
            88,
            118,
            158,
            198,
            238,
            278,
            318,
            358,
            398,
            438,
            478,
            518,
            558,
        ],
        '6' =>
        [
            // TVS King Deluxe
            8,
            23,
            38,
            53,
            68,
            83,
            98,
            113,
            128,
            143,
            158,
            173,
            190,
            203,
            218,
        ],
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

        return response()->json($returnArray, 200);
    }

    public function sendWhatsAppMessage($mobileNumber, $message)
    {
        $url = "https://wa.smsidea.com/api/v1/sendMessage";
        $whatsAppAPIKey = '6edc72b9f0884247ba0a630beb94c028';

        $data = [
            'key' => $whatsAppAPIKey,
            'to' => '91' . $mobileNumber,
            'message' => $message,
            'isUrgent' => true,
        ];
        $response = Http::withOptions(['verify' => false])->post($url, $data);
        if ($response->successful()) {
            $responseDecode = $response->json();
            if ($responseDecode['ErrorCode'] === '000') {
                return true;
            } else {
                return false;
            }
        }
    }

    public function sendWhatsAppMessageForLead($mobileNumber, $message)
    {
        $url = "https://wa.smsidea.com/api/v1/sendMessage";
        $whatsAppAPIKey = '6a6ccf290d9a4a7795810e05a752adeb';

        $data = [
            'key' => $whatsAppAPIKey,
            'to' => '91' . $mobileNumber,
            'message' => $message,
            'isUrgent' => true,
        ];
        $response = Http::withOptions(['verify' => false])->post($url, $data);
        if ($response->successful()) {
            $responseDecode = $response->json();
            if ($responseDecode['ErrorCode'] === '000') {
                return true;
            } else {
                return false;
            }
        }
    }

    public function sendWhatsAppMessageWithFile($mobileNumber, $message, $file, $fileName = '')
    {
        if (empty($fileName)) {
            $fileName = 'brochure.pdf';
        } else {
            $fileName = $fileName . '.pdf';
        }
        $url = "https://wa.smsidea.com/api/v1/sendDocument";
        $whatsAppAPIKey = '6edc72b9f0884247ba0a630beb94c028';
        $data = [
            'key' => $whatsAppAPIKey,
            'to' => '91' . $mobileNumber,
            'caption' => $message,
            'isUrgent' => true,
            "url" => $file,
            "filename" => $fileName
        ];
        $response = Http::withOptions(['verify' => false])->post($url, $data);
        if ($response->successful()) {
            $responseDecode = $response->json();

            if ($responseDecode['ErrorCode'] === '000') {
                return true;
            } else {
                return false;
            }
        }
    }

    public function sendWhatsAppMessageWithFileForLead($mobileNumber, $message, $file, $fileName = '')
    {
        if (empty($fileName)) {
            $fileName = 'brochure.pdf';
        } else {
            $fileName = $fileName . '.pdf';
        }
        $url = "https://wa.smsidea.com/api/v1/sendDocument";
        $whatsAppAPIKey = '6a6ccf290d9a4a7795810e05a752adeb';
        $data = [
            'key' => $whatsAppAPIKey,
            'to' => '91' . $mobileNumber,
            'caption' => $message,
            'isUrgent' => true,
            "url" => $file,
            "filename" => $fileName
        ];
        $response = Http::withOptions(['verify' => false])->post($url, $data);
        if ($response->successful()) {
            $responseDecode = $response->json();

            if ($responseDecode['ErrorCode'] === '000') {
                return true;
            } else {
                return false;
            }
        }
    }

    public function generateSecretFile($id)
    {
        $secretPath = base_path('app/Secrets/');

        if (!File::exists($secretPath)) {
            File::makeDirectory($secretPath, 0777, true, true);
        }


        $userFile = $secretPath . '/' . $id . '.php';
        if (File::exists($userFile)) {
            File::delete($userFile);
        }

        $userData = "<?php\n";

        $rights = UserRight::query()->where('user_id', $id)->get();
        $module = Module::query()->get();

        $userRightsData = [];

        $modules = Module::query()->whereIn('id', $rights->pluck('module_id'))->get();

        if ($module->count()) {
            foreach ($modules as $value) {
                $rights = UserRight::query()->where('module_id', $value->id)->where('user_id', $id)->get();
                if ($rights) {
                    foreach ($rights as $rightRow) {
                        if (!empty($value->config_key)) {
                            if (!empty($rightRow->role_add)) {
                                $userData .= "\r\n define('" . $value->config_key . "_ROLE_CREATE','1'); // constants for check rights";
                            }
                            if (!empty($rightRow->role_view)) {
                                $userData .= "\r\n define('" . $value->config_key . "_ROLE_VIEW','1'); // constants for check rights";
                            }
                            if (!empty($rightRow->role_viewAll)) {
                                $userData .= "\r\n define('" . $value->config_key . "_ROLE_VIEW_ALL','1'); // constants for check rights";
                            }
                            if (!empty($rightRow->role_edit)) {
                                $userData .= "\r\n define('" . $value->config_key . "_ROLE_EDIT','1'); // constants for check rights";
                            }
                            if (!empty($rightRow->role_delete)) {
                                $userData .= "\r\n define('" . $value->config_key . "_ROLE_DELETE','1'); // constants for check rights";
                            }
                        }
                    }
                }

                $userRightsData[$value->id] = [
                    'id' => $value->id,
                    'name' => $value->name,
                    'config_key' => $value->config_key,
                    'route_name' => $value->route_name ?? '',
                ];
            }
        }
        $userData .= "\n\ndefine('USER_MODULE_DATA', '" . serialize($userRightsData) . "')";
        $userData .= "\n\n?>";
        File::put($userFile, $userData);
    }

    // public function generateAndStorePdf($view = '', $data = null, $folder = 'amc_pdfs', $fileName = null)
    // {
    //     $pdf = Pdf::loadView($view, $data);

    //     if (!$fileName) {
    //         $fileName = 'amc_' . now()->format('Ymd_His') . '_' . $data['amc']->id . '.pdf';
    //     }

    //     $publicFolder = 'assets/temp';
    //     if (!file_exists($publicFolder)) {
    //         mkdir($publicFolder, 0775, true);
    //     }

    //     $fullPath = "{$publicFolder}/{$fileName}";
    //     file_put_contents($fullPath, $pdf->output());

    //     return [
    //         'full_path' => $fullPath,
    //         'public_url' => url("{$publicFolder}/{$fileName}"),
    //     ];
    // }

    public function generateAndStorePdf($view = '', $data = null, $folder = 'amc_pdfs', $fileName = null)
    {
        $pdf = Pdf::loadView($view, $data);

        if (!$fileName) {
            $fileName = 'amc_' . now()->format('Ymd_His') . '_' . $data['amc']->id . '.pdf';
        }

        // $publicFolder = 'assets/temp';
        if (env('SERVER_MODE') == 'live') {
            $publicHtmlAssetsPath = base_path('../public_html/amper/assets/temp');
        } else {
            $publicHtmlAssetsPath = base_path('../../public_html/ampere-testing/assets/temp');
        }

        if (!file_exists($publicHtmlAssetsPath)) {
            mkdir($publicHtmlAssetsPath, 0775, true);
        }

        $fullPath = $publicHtmlAssetsPath . '/' . $fileName;
        file_put_contents($fullPath, $pdf->output());

        return [
            'full_path' => $fullPath,
            'public_url' => 'https://chiragautomotive.com/amper/assets/temp/' . $fileName,
        ];
    }

    public function getLeadMessage($languageType, $name, $vehicleName, $salesmanName, $salesmanMobile, $isGeneral = false, $locationTypes = [])
    {
        $message = '';

        switch ($languageType) {
            case '1': // English
                if ($isGeneral) {
                    $message = "Hi {$name}\n\n";
                    $message .= "Thank you for showing your interest in our electric vehicles.\n\n";
                } else {
                    $message = "Hi {$name}\n\n";
                    $message .= "Thank you for showing your interest in *{$vehicleName}*.\n\n";
                }

                $message .= "My name is {$salesmanName} and I will be your companion along this electrifying journey.\n\n";

                if (!empty($locationTypes)) {
                    foreach ($locationTypes as $loc) {
                        if ($loc == 1) {
                            $message .= "Location (Sama Savli Road)\n\n";
                            $message .= "GF 23/24 Earth Eon\n";
                            $message .= "Opp Sama Lake\n";
                            $message .= "Opp Urmi School\n";
                            $message .= "Sama Savli Road\n";
                            $message .= "Vadodara - 390008\n\n";
                            $message .= "Google Map: https://share.google/V12FOd5tDP79YrMlS\n\n";
                        } elseif ($loc == 2) {
                            $message .= "Location (Kalali-Vadsar Road)\n\n";
                            $message .= "Abhishek Landmark\n";
                            $message .= "Opp Jagnath Mahadev Mandir\n";
                            $message .= "Near Khiskoli Circle\n";
                            $message .= "Kalali-Vadsar Road\n";
                            $message .= "Vadodara - 390012\n\n";
                            $message .= "Google Map: https://g.co/kgs/LAesMhy\n\n";
                        }
                    }
                }

                $message .= "Warm Regards\n";
                $message .= "{$salesmanName}\n";
                $message .= $salesmanMobile;
                break;

            case '2': // Gujarati
                if ($isGeneral) {
                    $message = "નમસ્કાર {$name}\n\n";
                    $message .= "અમારા ઇલેક્ટ્રિક વાહનોમાં રસ દર્શાવવા બદલ હૃદયપૂર્વક આભાર.\n\n";
                } else {
                    $message = "નમસ્કાર {$name}\n\n";
                    $message .= "તમારો *{$vehicleName}* માં રસ દર્શાવવા બદલ હૃદયપૂર્વક આભાર.\n\n";
                }

                $message .= "મારું નામ {$salesmanName} છે અને આ ઉત્સાહભરેલી મુસાફરીમાં હું આપનો સહયોગી રહીશ.\n\n";

                if (!empty($locationTypes)) {
                    foreach ($locationTypes as $loc) {
                        if ($loc == 1) {
                            $message .= "📍 સ્થાન (સમા-સાવલી રોડ)\n\n";
                            $message .= "જી.એફ. 23/24 અર્થ ઇઓન\n";
                            $message .= "સમા તળાવ સામે\n";
                            $message .= "ઉર્મિ સ્કૂલ સામે\n";
                            $message .= "સમા-સાવલી રોડ\n";
                            $message .= "વડોદરા - 390008\n\n";
                            $message .= "Google Map: https://share.google/V12FOd5tDP79YrMlS\n\n";
                        } elseif ($loc == 2) {
                            $message .= "📍 સ્થાન (કલાલી-વડસાર રોડ)\n\n";
                            $message .= "અભિષેક લૅન્ડમાર્ક\n";
                            $message .= "જાગનાથ મહાદેવ મંદિર સામે\n";
                            $message .= "ખિસકોલી સર્કલ નજીક\n";
                            $message .= "કલાલી-વડસાર રોડ\n";
                            $message .= "વડોદરા - 390012\n\n";
                            $message .= "Google Map: https://g.co/kgs/LAesMhy\n\n";
                        }
                    }
                }

                $message .= "સ્નેહપૂર્વક,\n";
                $message .= "{$salesmanName}\n";
                $message .= $salesmanMobile;
                break;

            default:
                return null;
        }

        return $message;
    }


    public function getLocationMessage($languageType, $locationType)
    {
        $message = '';

        switch ($languageType) {
            case '1': // English
                if ($locationType == 1) {
                    $message = "📍 Location (Sama Savli Road)\n\n";
                    $message .= "GF 23/24 Earth Eon\n";
                    $message .= "Opp Sama Lake\n";
                    $message .= "Opp Urmi School\n";
                    $message .= "Sama Savli Road\n";
                    $message .= "Vadodara - 390008";
                } elseif ($locationType == 2) {
                    $message = "📍 Location (Kalali-Vadsar Road)\n\n";
                    $message .= "Abhishek Landmark\n";
                    $message .= "Opp Jagnath Mahadev Mandir\n";
                    $message .= "Near Khiskoli Circle\n";
                    $message .= "Kalali-Vadsar Road\n";
                    $message .= "Vadodara - 390012";
                }
                break;

            case '2': // Gujarati
                if ($locationType == 1) {
                    $message = "📍 સ્થાન (સમા-સાવલી રોડ)\n\n";
                    $message .= "જી.એફ. 23/24 અર્થ ઇઓન\n";
                    $message .= "સમા તળાવ સામે\n";
                    $message .= "ઉર્મિ સ્કૂલ સામે\n";
                    $message .= "સમા-સાવલી રોડ\n";
                    $message .= "વડોદરા - 390008";
                } elseif ($locationType == 2) {
                    $message = "📍 સ્થાન (કાલાલી-વડસાર રોડ)\n\n";
                    $message .= "અભિષેક લૅન્ડમાર્ક\n";
                    $message .= "જગનાથ મહાદેવ મંદિર સામે\n";
                    $message .= "ખિસકોલી સર્કલ નજીક\n";
                    $message .= "કાલાલી-વડસાર રોડ\n";
                    $message .= "વડોદરા - 390012";
                }
                break;

            default:
                return null;
        }

        return $message;
    }

    function sendMetaWhatsappMessage($mobile, $template, $param = [], $fileUrl = '', $fileName = '')
    {
        $returnData = null;

        $url = $this->WHATSAPP_URL
            . '?LicenseNumber=' . $this->WHATSAPP_LICENSE_NUMBER
            . '&APIKey=' . $this->WHATSAPP_API_KEY
            . '&Contact=91' . $mobile
            . '&Template=' . $template;
        if (!empty($param)) {
            $safeParams = array_map(function ($value) {
                if (!is_string($value)) {
                    return $value;
                }
                return str_replace(',', ' ', trim($value));
            }, $param);

            $textParam = implode(',', $safeParams);
            $url .= '&Param=' . $textParam;
        }

        if (!empty($fileUrl)) {
            $url .= '&Fileurl=' . $fileUrl;
        }

        if (!empty($fileName)) {
            $url .= '&PDFName=' . $fileName;
        }
            // dd($url);

        try {
            $response = Http::withOptions(['verify' => false])->get($url);

            if (!$response->successful()) {
                $returnData = [
                    'status' => false,
                    'message' => 'HTTP request failed',
                    'response' => $response->body(),
                ];
            }

            $data = $response->json();

           // dd($data);
            if (
                isset($data['ApiResponse']) &&
                $data['ApiResponse'] === 'Success' &&
                isset($data['ApiMessage']['messages'][0]['id'])
            ) {
                $returnData = [
                    'status' => true,
                    'message' => 'WhatsApp message accepted',
                    'message_id' => $data['ApiMessage']['messages'][0]['id'],
                    'wa_id' => $data['ApiMessage']['contacts'][0]['wa_id'] ?? null,
                    'raw' => $data,
                ];
            }

            $returnData = [
                'status' => false,
                'message' => 'WhatsApp API error',
                'raw' => $data,
            ];
        } catch (\Exception $e) {
            $returnData = [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
        // dd($returnData);
        return $returnData;
    }

    function sendMetaMediaMessage($contact, $fileUrl, $type = 'audio')
    {
        $returnData = null;
        $url = "https://app.ampala.in/api/sendmediamessage.php?LicenseNumber=$this->WHATSAPP_LICENSE_NUMBER&APIKey=$this->WHATSAPP_API_KEY&Contact=$contact&Type=$type&FileURL=$fileUrl";

        try {
            $response = Http::withOptions(['verify' => false])->get($url);

            if (!$response->successful()) {
                $returnData = [
                    'status' => false,
                    'message' => 'HTTP request failed',
                    'response' => $response->body(),
                ];
            }

            $data = $response->json();
            if (
                isset($data['ApiResponse']) &&
                $data['ApiResponse'] === 'Success' &&
                isset($data['ApiMessage']['messages'][0]['id'])
            ) {
                $returnData = [
                    'status' => true,
                    'message' => 'WhatsApp message accepted',
                    'message_id' => $data['ApiMessage']['messages'][0]['id'],
                    'wa_id' => $data['ApiMessage']['contacts'][0]['wa_id'] ?? null,
                    'raw' => $data,
                ];
            }

            $returnData = [
                'status' => false,
                'message' => 'WhatsApp API error',
                'raw' => $data,
            ];
        } catch (\Exception $e) {
            $returnData = [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
        return $returnData;
    }
    function sanitizeWhatsappParam($value)
    {
        if (!is_string($value)) {
            return $value;
        }

        return trim(
            str_replace(
                [','],
                [' '], // replace comma with space
                $value
            )
        );
    }
}
