<?php

namespace App\Models;

use App\Http\Controllers\CommonFunctions;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AmcMaster extends Model
{
    use HasFactory, CommonFunctions;
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_by = Auth::id();
        });
        static::updating(function ($model) {
            $model->modified_by = Auth::id();
        });
    }
    protected $table = 'amc_masters';

    protected $fillable = [
        'amc_reference_id',
        'amc_package_type_id',
        'amc_display_number',
        'chassis_number',
        'vehicle_type',
        'vehicle_master_id',
        'vehicle_number',
        'customer_name',
        'contact_number',
        'contact_address',
        'amc_type',
        'amc_basic_price',
        'amc_start_date',
        'amc_end_date',
        'amc_start_km',
        'payment_type',
        'transaction_details',
        'no_of_service',
        'status',
        'renew_status',
        'created_by',
        'modified_by',
    ];

    protected $appends = [
        'display_amc_start_date',
        'display_amc_end_date',
        'vehicle_name',
        'amc_package_type_name',
        'vehicle_type_name',
        'payment_type_name',
        'status_name',
    ];
    /**
     * @param string[] $fillable
     */
    public function setFillable(array $fillable): void
    {
        $this->fillable = $fillable;
    }

    function services()
    {
        return $this->hasMany(ServiceDetail::class, 'amc_id', 'id');
    }

    function getDisplayAmcStartDateAttribute()
    {
        return Carbon::parse($this->amc_start_date)->format('d-M-Y');
    }

    function getDisplayAmcEndDateAttribute()
    {
        return Carbon::parse($this->amc_end_date)->format('d-M-Y');
    }

    function getVehicleNameAttribute()
    {
        $name = '';
        $query = Vehicle::find($this->vehicle_master_id, ['name']);
        if ($query) {
            $name = $query->name;
        }
        return $name;
    }

    function getAmcPackageTypeNameAttribute()
    {
        $name = '';
        $query = AmcPackageMaster::find($this->amc_package_type_id);
        if ($query) {
            $name = "$query->service_count services - $query->price duration $query->time_period months";
        }
        return $name;
    }
   

    function getVehicleTypeNameAttribute()
    {
        $name = $this->vehicleTypeArray[$this->vehicle_type];
        return $name;
    }

    function getPaymentTypeNameAttribute()
    {
        $name = $this->paymentTypeArray[$this->payment_type];
        return $name;
    }

    function getStatusNameAttribute()
    {
        return $this->statusArray[$this->status];
    }
    function getAmcPackageTypeDetailsAttribute()
    {
        $query = AmcPackageMaster::where('id',$this->amc_package_type_id)->first();
       
        return $query;
    }

    /**
     * Get TVS vehicle 3-service schedule (dates and KM).
     * Reference: AmcMasterController (store/update/renewHandel) - logic taken from there; do not change controller.
     * Used by TVSVehicleAmcServiceSeeder to update existing services to match Add AMC flow.
     * Returns array of [ 'date' => Carbon, 'km' => int ] for services 1, 2, 3.
     * Only valid for vehicle_master_id 4 (King EV Max), 5 (King Duramax Plus), 6 (King Deluxe).
     *
     * @return array<int, array{date: \Carbon\Carbon, km: int}>
     */
    public function getTVSServiceSchedule(): array
    {
        $contractStartDate = Carbon::parse($this->amc_start_date);
        $startKm = (int) ($this->amc_start_km ?? 0);
        $schedule = [];

        switch ((int) $this->vehicle_master_id) {
            case 6: // King Deluxe - 25/70/115 days, 750/4500/9500 km
                $schedule = [
                    ['date' => $contractStartDate->copy()->addDays(25), 'km' => $startKm + 750],
                    ['date' => $contractStartDate->copy()->addDays(70), 'km' => $startKm + 4500],
                    ['date' => $contractStartDate->copy()->addDays(115), 'km' => $startKm + 9500],
                ];
                break;
            case 5: // King Duramax Plus - 35/100/165 days, 750/9500/19500 km
                $schedule = [
                    ['date' => $contractStartDate->copy()->addDays(35), 'km' => $startKm + 750],
                    ['date' => $contractStartDate->copy()->addDays(100), 'km' => $startKm + 9500],
                    ['date' => $contractStartDate->copy()->addDays(165), 'km' => $startKm + 19500],
                ];
                break;
            case 4: // King EV Max - 40/85/175 days, 950/9500/19500 km
                $schedule = [
                    ['date' => $contractStartDate->copy()->addDays(40), 'km' => $startKm + 950],
                    ['date' => $contractStartDate->copy()->addDays(85), 'km' => $startKm + 9500],
                    ['date' => $contractStartDate->copy()->addDays(175), 'km' => $startKm + 19500],
                ];
                break;
            default:
                $schedule = [];
        }

        return $schedule;
    }
}
