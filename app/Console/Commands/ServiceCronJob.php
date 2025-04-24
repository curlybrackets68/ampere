<?php

namespace App\Console\Commands;

use App\Models\AmcMaster;
use App\Models\ServiceDetail;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ServiceCronJob extends Command
{
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
        $today = now()->startOfDay();
        $startDate = $today;
        $endDate = $today->copy()->addDays(7);

        $serviceRecords = ServiceDetail::where('status', 1)
            ->whereDate('service_date', '>=', $startDate)
            ->whereDate('service_date', '<=', $endDate)
            ->get();

        if ($serviceRecords->isNotEmpty()) {
            foreach ($serviceRecords as $service) {
                $amcQuery = AmcMaster::find($service->amc_id);
                $daysLeft = now()->diffInDays(Carbon::parse($service->amc_end_date), false);

                if (in_array($daysLeft, [7, 5, 3, 1, 0])) {
                    // $message = $daysLeft === 0
                    //     ? "Your AMC is expiring today"
                    //     : "Your AMC is expiring in {$daysLeft} day" . ($daysLeft != 1 ? 's' : '');
                    
                    $message = "Dear $amcQuery->customer_name,\n";
                    $message .= "This is a gentle reminder that your next AMC service is due soon for your vehicle ($amcQuery->vehicle_number).\n";
                    $message .= "Scheduled Date: $service->display_service_date \n";
                    $message .= "AMC Contract ID: $amcQuery->amc_display_number\n";
                    $message .= "Location: Ampere Service Center, Vadodara \n";
                    $message .= "Please send “Hi” on the number mentioned to book your appointment\n";
                    $message .= "9023342463 \n";
                    $message .= "Thank you for choosing Ampere! \n";
                    $message .= "For assistance, call us at +91 90233 42463.\n";


                    $this->info($message);
                    \Log::info($message);
                }
            }
        } else {
            $this->info("No Service expiry messages needed today.");
            \Log::info("No Service expiry messages needed today.");
        }
    }
}
