<?php

namespace Database\Seeders;

use App\Models\AmcMaster;
use App\Models\ServiceDetail;
use App\Models\SystemLogs;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TVSVehicleAmcServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * TVSVehicleAmcServiceSeeder does the following (for each TVS vehicle AMC):
     *
     * 1. DELETE all services except the first 3 — keeps only the first 3 services (by service_no/date/id), HARD DELETES all others.
     * 2. UPDATE service date and KM as per Add AMC — recalculates each of the 3 services using same logic as Add AMC (AmcMaster::getTVSServiceSchedule), and sets service_type = Free.
     * 3. UPDATE AMC contract end date — sets amc_end_date = last service date + 15 days.
     *
     * TVS Vehicles: vehicle_master_id 4 (King EV Max), 5 (King Duramax Plus), 6 (King Deluxe).
     *
     * WARNING: This seeder performs HARD DELETES — services are permanently removed and cannot be recovered!
     */
    public function run(): void
    {
        // TVS Vehicle Master IDs
        $tvsVehicleIds = [4, 5, 6];
        
        echo "\n=== TVS Vehicle Service Cleanup Seeder ===\n";
        echo "⚠️  WARNING: This seeder performs HARD DELETES - services will be permanently removed!\n";
        echo "This will:\n";
        echo "1. Keep only the first 3 services for each TVS vehicle AMC\n";
        echo "2. HARD DELETE all other services (permanently removed from database)\n";
        echo "3. Fix service dates/KM from AMC start date and set Service Type = Free for all 3 services\n";
        echo "4. Update amc_end_date = last service date + 15 days\n";
        echo "TVS Vehicles: King EV Max (4), King Duramax Plus (5), King Deluxe (6)\n\n";
        
        // Get TVS Vehicles
        $tvsVehicles = Vehicle::whereIn('id', $tvsVehicleIds)->get();
        
        echo "=== TVS Vehicles Found ===\n";
        echo "Total TVS Vehicles: " . $tvsVehicles->count() . "\n";
        foreach ($tvsVehicles as $vehicle) {
            echo "ID: {$vehicle->id} | Name: {$vehicle->name}\n";
        }
        
        // Get AMC records for TVS vehicles
        $tvsAmcIds = AmcMaster::whereIn('vehicle_master_id', $tvsVehicleIds)
            ->pluck('id')
            ->toArray();
        
        if (empty($tvsAmcIds)) {
            echo "\nNo TVS vehicle AMCs found. Exiting...\n";
            return;
        }
        
        echo "\n=== Processing TVS AMCs ===\n";
        echo "Total TVS AMCs: " . count($tvsAmcIds) . "\n\n";
        
        $totalDeleted = 0;
        $totalKept = 0;
        $processedAmcs = 0;
        $amcsUpdated = 0;
        $servicesRecalculated = 0;
        
        // Process each TVS AMC
        foreach ($tvsAmcIds as $amcId) {
            $amc = AmcMaster::find($amcId);
            
            if (!$amc) {
                continue;
            }
            
            // Get all services for this AMC
            $allServices = ServiceDetail::where('amc_id', $amcId)
                ->orderBy('service_no', 'ASC')
                ->orderBy('service_date', 'ASC')
                ->orderBy('id', 'ASC')
                ->get();
            
            if ($allServices->count() > 3) {
                // Get first 3 services to keep
                $firstThreeServices = $allServices->take(3);
                $keepServiceIds = $firstThreeServices->pluck('id')->toArray();
                
                // Get services to delete (all except first 3)
                $servicesToDelete = $allServices->whereNotIn('id', $keepServiceIds);
                $deleteServiceIds = $servicesToDelete->pluck('id')->toArray();
                
                if (count($deleteServiceIds) > 0) {
                    // Hard delete the services (permanently remove from database)
                    // Log deleted services before deletion (since we need the ID)
                    foreach ($servicesToDelete as $service) {
                        SystemLogs::create([
                            'inquiry_id' => 0,
                            'type' => '6', // Service Module ID
                            'type_id' => $service->id,
                            'remark' => 'Service hard deleted by seeder - keeping only first 3 services for TVS vehicle AMC',
                            'action_id' => 3, // Delete action
                            'created_by' => 1, // System user
                        ]);
                    }
                    
                    // Hard delete services permanently from database
                    $deletedCount = ServiceDetail::whereIn('id', $deleteServiceIds)->forceDelete();
                    $totalDeleted += $deletedCount;
                    $totalKept += count($keepServiceIds);
                    $processedAmcs++;
                    
                    echo "AMC ID: {$amcId} ({$amc->amc_display_number})\n";
                    echo "  Vehicle: {$amc->vehicle_name} | Customer: {$amc->customer_name}\n";
                    echo "  Total Services: {$allServices->count()} | Keeping: 3 | Hard Deleting: {$deletedCount}\n";
                    echo "  ✓ Hard deleted {$deletedCount} services (permanently removed from database)\n";
                }
            } else {
                echo "AMC ID: {$amcId} ({$amc->amc_display_number}) - Already has 3 or fewer services.\n";
            }
            
            // Get remaining services after deletion (first 3), ordered by service_no
            $remainingServices = ServiceDetail::where('amc_id', $amcId)
                ->orderBy('service_no', 'ASC')
                ->orderBy('id', 'ASC')
                ->get();
            
            // For TVS: Update all 3 service dates/KM using same flow as Add AMC (AmcMaster::getTVSServiceSchedule)
            if ($remainingServices->count() > 0 && in_array((int) $amc->vehicle_master_id, [4, 5, 6])) {
                $tvsSchedule = $amc->getTVSServiceSchedule();
                if (count($tvsSchedule) > 0) {
                    foreach ($remainingServices as $index => $service) {
                        if ($index >= 3) {
                            break;
                        }
                        $serviceNumber = $index + 1;
                        $item = $tvsSchedule[$index] ?? null;
                        if (!$item) {
                            continue;
                        }
                        $newServiceDate = $item['date'];
                        $newServiceKm = $item['km'];
                        
                        $oldDate = Carbon::parse($service->service_date)->format('d-M-Y');
                        $oldKm = $service->service_km;
                        $oldType = $service->service_type;
                        
                        $service->update([
                            'service_no' => $serviceNumber,
                            'service_date' => $newServiceDate->format('Y-m-d H:i:s'),
                            'service_km' => $newServiceKm,
                            'service_type' => 1, // Free - all TVS AMC services are Free
                        ]);
                        
                        $servicesRecalculated++;
                        $typeNote = ($oldType != 1) ? ", Type: Paid → Free" : "";
                        echo "  ✓ Service #{$serviceNumber}: Date {$oldDate} → {$newServiceDate->format('d-M-Y')}, KM {$oldKm} → {$newServiceKm}{$typeNote}\n";
                    }
                }
            }
            
            // Update AMC end date: last service date + 15 days (for all AMCs)
            $lastServiceDate = ServiceDetail::where('amc_id', $amcId)
                ->orderBy('service_date', 'DESC')
                ->orderBy('id', 'DESC')
                ->first();
            
            if ($lastServiceDate) {
                $oldEndDate = $amc->amc_end_date ? Carbon::parse($amc->amc_end_date)->format('d-M-Y') : 'N/A';
                $newAmcEndDate = Carbon::parse($lastServiceDate->service_date)->addDays(15);
                
                // Use DB::table to bypass model boot method that requires Auth::id()
                DB::table('amc_masters')
                    ->where('id', $amcId)
                    ->update([
                        'amc_end_date' => $newAmcEndDate->format('Y-m-d H:i:s'),
                        'modified_by' => 1, // System user for seeder
                        'updated_at' => now()
                    ]);
                
                $amc->refresh();
                $amcsUpdated++;
                
                $newEndDate = Carbon::parse($amc->amc_end_date)->format('d-M-Y');
                echo "  ✓ Updated amc_end_date: {$oldEndDate} → {$newEndDate} (last service date + 15 days)\n\n";
            } else {
                echo "  ⚠ No services found for this AMC. Cannot update amc_end_date.\n\n";
            }
        }
        
        echo "\n=== Summary ===\n";
        echo "Total TVS Vehicles: " . $tvsVehicles->count() . "\n";
        echo "Total TVS AMCs: " . count($tvsAmcIds) . "\n";
        echo "AMCs with Services Deleted: {$processedAmcs}\n";
        echo "Services Kept: {$totalKept}\n";
        echo "Services Deleted: {$totalDeleted}\n";
        echo "Services Recalculated: {$servicesRecalculated}\n";
        echo "AMCs with Updated End Date: {$amcsUpdated}\n";
        echo "\n✓ Seeder completed successfully!\n\n";
    }
}
