<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AmcMaster extends Model
{
    use HasFactory;
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
        'amc_display_number',
        'chassis_number',
        'vehicle_type',
        'vehicle_master_id',
        'vehicle_number',
        'customer_name',
        'contact_number',
        'amc_type',
        'amc_basic_price',
        'amc_start_date',
        'amc_end_date',
        'payment_type',
        'transaction_details',
        'no_of_service',
        'branch_id',
        'status',
        'created_by',
        'modified_by',
    ];

    /**
     * @param string[] $fillable
     */
    public function setFillable(array $fillable): void
    {
        $this->fillable = $fillable;
    }

}
