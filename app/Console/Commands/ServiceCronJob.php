<?php

namespace App\Console\Commands;

use App\Http\Controllers\CommonFunctions;
use App\Models\AmcMaster;
use App\Models\ServiceDetail;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ServiceCronJob extends Command
{
    use CommonFunctions;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:service-cronjob';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'When Service Due (Upcoming) 7 day before and every 2 day';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // When Service Due (Upcoming) 7 day before and every 2 day

        $today = Carbon::today();
        $startDate = $today->toDateString();
        $endDate = $today->copy()->addDays(7)->toDateString();

        $serviceRecords = ServiceDetail::where('status', 1)
            ->whereDate('service_date', '>=', $startDate)
            ->whereDate('service_date', '<=', $endDate)
            ->get();

        if ($serviceRecords->isNotEmpty()) {
            foreach ($serviceRecords as $service) {
                $amcQuery = AmcMaster::where('id', $service->amc_id)
                    ->whereNotIn('vehicle_master_id', [4, 5, 6])
                    ->first();

                if ($amcQuery) {
                    $daysLeft = now()->diffInDays(Carbon::parse($service->service_date), false);

                    $pdfUrl = $this->generateAndStorePdf('pdf.amc-pdf', ['amc' => $amcQuery], 'amc_pdfs');

                    if (in_array($daysLeft, [7, 5, 3, 1])) {
                        // $message = $daysLeft === 0
                        //     ? "Your AMC is expiring today"
                        //     : "Your AMC is expiring in {$daysLeft} day" . ($daysLeft != 1 ? 's' : '');

                        $message = "Dear $amcQuery->customer_name,\n";
                        $message .= "This is a gentle reminder that your next AMC service is due soon for your vehicle ($amcQuery->vehicle_number).\n";
                        $message .= "Scheduled Date: *$service->display_service_date* \n";
                        $message .= "AMC Contract ID: *$amcQuery->amc_display_number*\n";
                        $message .= "Location: *Ampere Service Center, Vadodara* \n";
                        $message .= "Please send “*Hi*” on the number mentioned to book your appointment\n";
                        $message .= "*9023342463* \n";
                        $message .= "Thank you for choosing Ampere! \n";
                        $message .= "For assistance, call us at +91 90233 42463.\n";

                        // $sent = $this->sendWhatsAppMessageWithFile($amcQuery->contact_number, $message, $pdfUrl['public_url']);
                        // $sent = $this->sendWhatsAppMessageWithFile($amcQuery->contact_number, $message, $pdfUrl['public_url'], 'amc_pdf');

                        // Meta Send
                        $metaData = [
                            $amcQuery->customer_name ?? 'N/A',
                            $amcQuery->vehicle_number,
                            $service->display_service_date ?? 'N/A',
                            $amcQuery->amc_display_number ?? 'N/A',
                        ];

                        $sent = $this->sendMetaWhatsappMessage($amcQuery->contact_number, 'amc_service_due_reminder', $metaData, $pdfUrl['public_url'], 'AMC_FILE');

                        if ($sent && File::exists($pdfUrl['public_url'])) {
                            File::delete($pdfUrl['public_url']);
                        }
                        $this->info($message);
                        \Log::info($message);
                    }
                }
            }
        } else {
            $this->info("No Service expiry messages needed today.");
            \Log::info("No Service expiry messages needed today.");
        }


        // When Service Due on date 

        $serviceRecordsOnDate = ServiceDetail::where('status', 1)
            ->whereDate('service_date', '=', $startDate)
            ->get();

        if ($serviceRecordsOnDate->isNotEmpty()) {
            foreach ($serviceRecordsOnDate as $service) {
                $amcQueryDue = AmcMaster::where('id', $service->amc_id)
                    ->whereNotIn('vehicle_master_id', [4, 5, 6])
                    ->first();

                if ($amcQueryDue) {
                    $daysLeft = now()->diffInDays(Carbon::parse($service->service_date), false);

                    $pdfUrl = $this->generateAndStorePdf('pdf.amc-pdf', ['amc' => $amcQueryDue], 'amc_pdfs');

                    $messageOnDue = "Dear $amcQueryDue->customer_name, \n\n";
                    $messageOnDue .= "We noticed that your AMC service for vehicle *$amcQueryDue->vehicle_number* was due on $service->display_service_date but hasn't been completed yet. \n";
                    $messageOnDue .= " \n";
                    $messageOnDue .= "Your AMC Contract *$amcQueryDue->amc_display_number* is still active, and we want to ensure your vehicle receives timely maintenance for optimal performance. \n";
                    $messageOnDue .= " \n";
                    $messageOnDue .= "Please note that irregular servicing can cause lapse of warranty benefits and AMC benefits \n";
                    $messageOnDue .= " \n";
                    $messageOnDue .= "Kindly contact us to schedule  your service or request. \n";
                    $messageOnDue .= " \n";
                    $messageOnDue .= "Thank you for choosing Ampere.   \n";
                    $messageOnDue .= "For assistance, call +91 90233 42463. \n";

                    // $sent = $this->sendWhatsAppMessageWithFile($amcQuery->contact_number, $messageOnDue, $pdfUrl['public_url']);
                    // $sent = $this->sendWhatsAppMessageWithFile($amcQueryDue->contact_number, $messageOnDue, $pdfUrl['public_url'], 'amc_pdf');

                    // Meta Send
                    $metaData = [
                        $amcQueryDue->customer_name ?? 'N/A',
                        $amcQueryDue->vehicle_number,
                        $service->display_service_date ?? 'N/A',
                        $amcQueryDue->amc_display_number ?? 'N/A',
                    ];

                    $sent = $this->sendMetaWhatsappMessage($amcQueryDue->contact_number, 'amc_service_due_on_date_english_v2', $metaData, $pdfUrl['public_url'], 'AMC_FILE');


                    if ($sent && File::exists($pdfUrl['public_url'])) {
                        File::delete($pdfUrl['public_url']);
                    }
                    $this->info($messageOnDue);
                    \Log::info($messageOnDue);
                }
            }
        } else {
            $this->info("No Service On Date expiry messages needed today.");
            \Log::info("No Service On Date expiry messages needed today.");
        }

        // When Service Due (After) every day 

        $serviceRecordsDue = ServiceDetail::where('status', 1)
            ->whereDate('service_date', '<', $startDate)
            ->get();

        if ($serviceRecordsDue->isNotEmpty()) {
            foreach ($serviceRecordsDue as $service) {
                $amcQueryServiceDue = AmcMaster::where('id', $service->amc_id)
                    ->whereNotIn('vehicle_master_id', [4, 5, 6])
                    ->first();
                if ($amcQueryServiceDue) {
                    $pdfUrl = $this->generateAndStorePdf('pdf.amc-pdf', ['amc' => $amcQueryServiceDue], 'amc_pdfs');

                    $messageDue = "Dear $amcQueryServiceDue->customer_name, \n\n";
                    $messageDue .= "This is a final reminder regarding your pending AMC service for vehicle *$amcQueryServiceDue->vehicle_number* under Contract ID: *$amcQueryServiceDue->amc_display_number*. \n\n";
                    $messageDue .= "Your scheduled service date *$service->display_service_date* has passed, and timely maintenance is essential to keep your vehicle running smoothly and to ensure AMC benefits remain valid. \n\n";
                    $messageDue .= "Please contact us immediately to schedule your service  \n\n";
                    $messageDue .= "Note: Delay in service may impact your AMC coverage. \n\n";
                    $messageDue .= "Thank you for choosing Ampere.   \n";
                    $messageDue .= "Support: +91 90233 42463  \n";

                    // $sent = $this->sendWhatsAppMessageWithFile($amcQuery->contact_number, $messageDue, $pdfUrl['public_url']);
                    // $sent = $this->sendWhatsAppMessageWithFile($amcQueryServiceDue->contact_number, $messageDue, $pdfUrl['public_url'], 'amc_pdf');

                    // Meta Send
                    $metaData = [
                        $amcQueryServiceDue->customer_name ?? 'N/A',
                        $amcQueryServiceDue->vehicle_number,
                        $service->display_service_date ?? 'N/A',
                        $amcQueryServiceDue->amc_display_number ?? 'N/A',
                    ];

                    $sent = $this->sendMetaWhatsappMessage($amcQueryServiceDue->contact_number, 'amc_service_overdue_final', $metaData, $pdfUrl['public_url'], 'AMC_FILE');

                    if ($sent && File::exists($pdfUrl['public_url'])) {
                        File::delete($pdfUrl['public_url']);
                    }
                    $this->info($messageDue);
                    \Log::info($messageDue);
                }
            }
        } else {
            $this->info("No Service Due expiry messages needed today.");
            \Log::info("No Service Due expiry messages needed today.");
        }

        // For '4', '5', '6' Vehicle Only

        $vehicleServiceRecords = AmcMaster::where('status', 10)
            ->whereIn('vehicle_master_id', [4, 5, 6])
            ->where('renew_status', 12)
            ->get();

        foreach ($vehicleServiceRecords as $amc) {
            $amcDate = Carbon::parse($amc->amc_start_date);

            $services = ServiceDetail::where('amc_id', $amc->id)
                ->orderBy('service_no', 'asc')
                ->get();

            foreach ($services as $service) {
                $reminderStartDay = (int) $service->reminder_days;

                if (!$reminderStartDay) {
                    \Log::info("No reminder day for Service #{$service->service_no}, AMC ID {$amc->id}");
                    continue;
                }

                $reminderStartDate = $amcDate->copy()->addDays($reminderStartDay);

                \Log::info($reminderStartDay);
                \Log::info($reminderStartDate);

                // 1
                if ($today->greaterThanOrEqualTo($reminderStartDate) && $service->status != 2) {

                    $gujaratiMessage = "પ્રિય {$amc->customer_name},\n";
                    $gujaratiMessage .= "આ એક નમ્ર યાદ અપાવવાનું સંદેશ છે કે તમારા વાહન ({$amc->vehicle_number}) માટેની આગામી AMC સેવા ટૂંક સમયમાં બાકી છે.\n";
                    $gujaratiMessage .= "નિર્ધારિત તારીખ: *{$service->display_service_date}* \n";
                    $gujaratiMessage .= "AMC કરાર ID: *{$amc->amc_display_number}*\n";
                    $gujaratiMessage .= "સ્થળ: *TVS સર્વિસ સેન્ટર, વડોદરા* \n";
                    $gujaratiMessage .= "તમારી એપોઇન્ટમેન્ટ બુક કરવા માટે કૃપા કરીને ઉલ્લેખિત નંબર પર “*Hi*” મોકલો.\n";
                    $gujaratiMessage .= "*9023342463* \n";
                    $gujaratiMessage .= "TVS પસંદ કરવા બદલ આપનો આભાર! \n";
                    $gujaratiMessage .= "મદદ માટે, અમને +૯૧ ૯૦૨૩૩ ૪૨૪૬૩ પર કોલ કરો.\n";

                    // $this->sendWhatsAppMessage($amc->contact_number, $gujaratiMessage);

                    // Meta Send
                    $metaData = [
                        $amc->customer_name ?? 'N/A',
                        $amc->vehicle_number,
                        $service->display_service_date ?? 'N/A',
                        $amc->amc_display_number ?? 'N/A',
                    ];

                    $this->sendMetaWhatsappMessage($amc->contact_number, 'amc_upcoming_service_gujarati', $metaData);

                    $this->info("Reminder sent for AMC ID {$amc->id}, Service #{$service->service_no}");
                    \Log::info("Reminder sent for AMC ID {$amc->id}, Service #{$service->service_no}");
                }

                // When Service Due on date
                if ($today->isSameDay($reminderStartDate) && $service->status != 2) {

                    $gujaratiMessageOnDue = "પ્રિય {$amc->customer_name}, \n\n";
                    $gujaratiMessageOnDue .= "અમે નોંધ્યું કે તમારા વાહન *{$amc->vehicle_number}* માટેની AMC સેવા $service->display_service_date ના રોજ બાકી હતી પરંતુ હજુ પૂર્ણ થઈ નથી. \n";
                    $gujaratiMessageOnDue .= " \n";
                    $gujaratiMessageOnDue .= "તમારો AMC કરાર *{$amc->amc_display_number}* હજુ સક્રિય છે, અને અમે ખાતરી કરવા માંગીએ છીએ કે તમારા વાહનને યોગ્ય કામગીરી માટે સમયસર મેઈન્ટેનન્સ મળે. \n";
                    $gujaratiMessageOnDue .= " \n";
                    $gujaratiMessageOnDue .= "કૃપા કરીને નોંધો કે અનિયમિત સેવા તમારા વોરંટી લાભો અને AMC લાભોને અસર કરી શકે છે. \n";
                    $gujaratiMessageOnDue .= " \n";
                    $gujaratiMessageOnDue .= "કૃપા કરીને અમારી સાથે સંપર્ક કરો જેથી તમારી સેવા અથવા વિનંતી માટે નિમણૂક બુક કરી શકાય. \n";
                    $gujaratiMessageOnDue .= " \n";
                    $gujaratiMessageOnDue .= "TVS પસંદ કરવા બદલ આપનો આભાર. \n";
                    $gujaratiMessageOnDue .= "મદદ માટે, +૯૧ ૯૦૨૩૩ ૪૨૪૬૩ પર કોલ કરો. \n";

                    // $this->sendWhatsAppMessage($amc->contact_number, $gujaratiMessageOnDue);

                    // Meta Send
                    $metaData = [
                        $amc->customer_name ?? 'N/A',
                        $amc->vehicle_number,
                        $service->display_service_date ?? 'N/A',
                        $amc->amc_display_number ?? 'N/A',
                    ];

                    $this->sendMetaWhatsappMessage($amc->contact_number, 'amc_service_due_gujarati', $metaData);

                    $this->info("🔔 Due Today: Reminder sent for AMC ID {$amc->id}, Service #{$service->service_no}");
                    \Log::info("🔔 Due Today: Reminder sent for AMC ID {$amc->id}, Service #{$service->service_no}");
                }

                // When Service Due (After) every day
                if ($today->greaterThan($reminderStartDate) && $service->status != 2) {

                    $gujaratiMessageDue = "પ્રિય {$amc->customer_name},\n\n";
                    $gujaratiMessageDue .= "આ તમારા બાકી AMC સેવા માટેની અંતિમ યાદ અપાવવાનું સંદેશ છે, જે વાહન *{$amc->vehicle_number}* હેઠળ કરાર ID: *{$amc->amc_display_number}* માં બાકી છે.\n\n";
                    $gujaratiMessageDue .= "તમારી નિર્ધારિત સર્વિસ તારીખ *{$service->display_service_date}* પસાર થઈ ગઈ છે, અને સમયસર મેઈન્ટેનન્સ જરૂરી છે જેથી તમારું વાહન સારી સ્થિતિમાં ચાલતું રહે અને AMC લાભો માન્ય રહે.\n\n";
                    $gujaratiMessageDue .= "કૃપા કરીને તરત અમારી સાથે સંપર્ક કરો અને તમારી સેવા માટે નિમણૂક બુક કરો.\n\n";
                    $gujaratiMessageDue .= "નોંધ: સેવા માં મોડાશી તમારી AMC કવરેજ પર અસર કરી શકે છે.\n\n";
                    $gujaratiMessageDue .= "TVS પસંદ કરવા બદલ આપનો આભાર.\n";
                    $gujaratiMessageDue .= "મદદ માટે, +૯૧ ૯૦૨૩૩ ૪૨૪૬૩ પર કોલ કરો.\n";

                    // $this->sendWhatsAppMessage($amc->contact_number, $gujaratiMessageDue);

                    // Meta Send
                    $metaData = [
                        $amc->customer_name ?? 'N/A',
                        $amc->vehicle_number,
                        $amc->amc_display_number ?? 'N/A',
                        $service->display_service_date ?? 'N/A',
                    ];

                    $this->sendMetaWhatsappMessage($amc->contact_number, 'amc_service_final_due_gujarati', $metaData);

                    $this->info("⚠️ Overdue: Reminder sent for AMC ID {$amc->id}, Service #{$service->service_no}");
                    \Log::info("⚠️ Overdue: Reminder sent for AMC ID {$amc->id}, Service #{$service->service_no}");
                }
            }
        }
    }
}
