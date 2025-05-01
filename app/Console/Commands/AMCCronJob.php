<?php

namespace App\Console\Commands;

use App\Http\Controllers\CommonFunctions;
use App\Models\AmcMaster;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class AMCCronJob extends Command
{
    use CommonFunctions;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:amc-cronjob';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'When AMC Due (Upcoming) 7 day before and every 2 day';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // When AMC Due (Upcoming) 7 day before and every 2 day

        $today = now()->startOfDay();
        $startDate = $today;
        $endDate = $today->copy()->addDays(7);

        $amcRecords = AmcMaster::where('status', 10)
            ->where('renew_status', 12)
            ->whereDate('amc_end_date', '>=', $startDate)
            ->whereDate('amc_end_date', '<=', $endDate)
            ->get();

        if ($amcRecords->isNotEmpty()) {
            foreach ($amcRecords as $amc) {
                $daysLeft = now()->diffInDays(Carbon::parse($amc->amc_end_date), false);
                $pdfUrl = $this->generateAndStorePdf('pdf.amc-pdf', ['amc' => $amc], 'amc_pdfs');
                if (in_array($daysLeft, [7, 5, 3, 1, 0])) {
                    // $message = $daysLeft === 0
                    //     ? "Your AMC is expiring today"
                    //     : "Your AMC is expiring in {$daysLeft} day" . ($daysLeft != 1 ? 's' : '');

                    $message = "Dear $amc->customer_name,\n\n";
                    $message .= "We hope your experience with Ampere AMC service has been smooth and satisfying.\n\n";
                    $message .= "Your AMC contract ID : $amc->amc_display_number for vehicle *$amc->vehicle_number* is due for renewal:\n\n";
                    $message .= "Expiry Date: *$amc->display_amc_end_date* \n";
                    $message .= "Vehicle Model: *$amc->vehicle_name*   \n\n";
                    $message .= "Renew now to continue enjoying priority service, hassle-free maintenance, and peace of mind. \n\n";
                    $message .= "To renew your AMC, reply to this message or call us at +91 90233 42463. \n\n";
                    $message .= "Thank you for trusting Ampere! \n";

                    // $sent = $this->sendWhatsAppMessageWithFile($amc->contact_number, $message, $pdfUrl['full_path']);

                    $sent = $this->sendWhatsAppMessageWithFile($amc->contact_number, $message, $pdfUrl['public_url'], 'amc_pdf');
                    if ($sent && File::exists($pdfUrl['public_url'])) {
                        File::delete($pdfUrl['public_url']);
                    }
                    $this->info($message);
                    \Log::info($message);
                }
            }
        } else {
            $this->info("No AMC expiry messages needed today.");
            \Log::info("No AMC expiry messages needed today.");
        }


        // When AMC Due (After) 

        $amcRecordsDue = AmcMaster::where('status', 11)
            ->where('renew_status', 12)
            ->whereDate('amc_end_date', '<', $today)
            ->get();
        if ($amcRecordsDue->isNotEmpty()) {
            foreach ($amcRecordsDue as $amcDue) {
                $pdfUrl = $this->generateAndStorePdf('pdf.amc-pdf', ['amc' => $amcDue], 'amc_pdfs');

                $messageDue = "Dear $amcDue->customer_name,\n";
                $messageDue .= "Just a friendly reminder — your AMC contract *$amcDue->amc_display_number* for vehicle *$amcDue->vehicle_number* is expiring soon on \n";
                $messageDue .= "*$amcDue->display_amc_end_date*.\n";
                $messageDue .= "\n";
                $messageDue .= "Renew now to avoid service interruptions and keep your vehicle in top condition.\n";
                $messageDue .= "\n";
                $messageDue .= "Benefits:\n";
                $messageDue .= "- Free routine maintenance  \n";
                $messageDue .= "- Priority service slots  \n";
                $messageDue .= "\n";
                $messageDue .= "To renew, simply reply to this message or call us at +91 90233 42463.\n";
                $messageDue .= "\n";
                $messageDue .= "Thank you for trusting Ampere!\n";

                // $sent = $this->sendWhatsAppMessageWithFile($amcDue->contact_number, $messageDue, $pdfUrl['full_path']);

                $sent = $this->sendWhatsAppMessageWithFile($amcDue->contact_number, $messageDue, $pdfUrl['public_url'], 'amc_pdf');
                if ($sent && File::exists($pdfUrl['public_url'])) {
                    File::delete($pdfUrl['public_url']);
                }
                $this->info($messageDue);
                \Log::info($messageDue);
            }
        } else {
            $this->info("No AMC Due expiry messages needed today.");
            \Log::info("No AMC Due expiry messages needed today.");
        }
    }
}
