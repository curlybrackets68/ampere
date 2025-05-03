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
}
