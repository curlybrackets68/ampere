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
                $amcQuery = AmcMaster::find($service->amc_id);
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
                    $sent = $this->sendWhatsAppMessageWithFile($amcQuery->contact_number, $message, $pdfUrl['public_url'], 'amc_pdf');
                    if ($sent && File::exists($pdfUrl['public_url'])) {
                        File::delete($pdfUrl['public_url']);
                    }
                    $this->info($message);
                    \Log::info($message);
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
                $amcQuery = AmcMaster::find($service->amc_id);
                $daysLeft = now()->diffInDays(Carbon::parse($service->service_date), false);

                $pdfUrl = $this->generateAndStorePdf('pdf.amc-pdf', ['amc' => $amcQuery], 'amc_pdfs');

                $messageOnDue = "Dear $amcQuery->customer_name, \n\n";
                $messageOnDue .= "We noticed that your AMC service for vehicle *$amcQuery->vehicle_number* was due on $service->display_service_date but hasn't been completed yet. \n";
                $messageOnDue .= " \n";
                $messageOnDue .= "Your AMC Contract *$amcQuery->amc_display_number* is still active, and we want to ensure your vehicle receives timely maintenance for optimal performance. \n";
                $messageOnDue .= " \n";
                $messageOnDue .= "Please note that irregular servicing can cause lapse of warranty benefits and AMC benefits \n";
                $messageOnDue .= " \n";
                $messageOnDue .= "Kindly contact us to schedule  your service or request. \n";
                $messageOnDue .= " \n";
                $messageOnDue .= "Thank you for choosing Ampere.   \n";
                $messageOnDue .= "For assistance, call +91 90233 42463. \n";

                // $sent = $this->sendWhatsAppMessageWithFile($amcQuery->contact_number, $messageOnDue, $pdfUrl['public_url']);
                $sent = $this->sendWhatsAppMessageWithFile($amcQuery->contact_number, $messageOnDue, $pdfUrl['public_url'], 'amc_pdf');
                if ($sent && File::exists($pdfUrl['public_url'])) {
                    File::delete($pdfUrl['public_url']);
                }
                $this->info($messageOnDue);
                \Log::info($messageOnDue);
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
                $amcQuery = AmcMaster::find($service->amc_id);
                $pdfUrl = $this->generateAndStorePdf('pdf.amc-pdf', ['amc' => $amcQuery], 'amc_pdfs');
                
                $messageDue = "Dear $amcQuery->customer_name, \n\n";
                $messageDue .= "This is a final reminder regarding your pending AMC service for vehicle *$amcQuery->vehicle_number* under Contract ID: *$amcQuery->amc_display_number*. \n\n";
                $messageDue .= "Your scheduled service date *$service->display_service_date* has passed, and timely maintenance is essential to keep your vehicle running smoothly and to ensure AMC benefits remain valid. \n\n";
                $messageDue .= "Please contact us immediately to schedule your service  \n\n";
                $messageDue .= "Note: Delay in service may impact your AMC coverage. \n\n";
                $messageDue .= "Thank you for choosing Ampere.   \n";
                $messageDue .= "Support: +91 90233 42463  \n";
                // $sent = $this->sendWhatsAppMessageWithFile($amcQuery->contact_number, $messageDue, $pdfUrl['public_url']);
                $sent = $this->sendWhatsAppMessageWithFile($amcQuery->contact_number, $messageDue, $pdfUrl['public_url'], 'amc_pdf');
                if ($sent && File::exists($pdfUrl['public_url'])) {
                    File::delete($pdfUrl['public_url']);
                }
                $this->info($messageDue);
                \Log::info($messageDue);
            }
        } else {
            $this->info("No Service Due expiry messages needed today.");
            \Log::info("No Service Due expiry messages needed today.");
        }
    }
}
