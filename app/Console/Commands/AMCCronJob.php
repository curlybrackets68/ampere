<?php

namespace App\Console\Commands;

use App\Models\AmcMaster;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AMCCronJob extends Command
{
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

                if (in_array($daysLeft, [7, 5, 3, 1, 0])) {
                    // $message = $daysLeft === 0
                    //     ? "Your AMC is expiring today"
                    //     : "Your AMC is expiring in {$daysLeft} day" . ($daysLeft != 1 ? 's' : '');
                    
                    $message = "Dear $amc->customer_name,\n";
                    $message .= "We hope your experience with Ampere AMC service has been smooth and satisfying.\n";
                    $message .= "Your AMC contract ID : $amc->amc_display_number for vehicle $amc->vehicle_number is due for renewal:\n";
                    $message .= "Expiry Date: $amc->display_amc_end_date \n";
                    $message .= "Vehicle Model: $amc->vehicle_name   \n";
                    $message .= "Renew now to continue enjoying priority service, hassle-free maintenance, and peace of mind. \n";
                    $message .= "To renew your AMC, reply to this message or call us at +91 90233 42463. \n";
                    $message .= "Thank you for trusting Ampere! \n";
                    $this->info($message);
                    \Log::info($message);
                }
            }
        } else {
            $this->info("No AMC expiry messages needed today.");
            \Log::info("No AMC expiry messages needed today.");
        }
    }
}
