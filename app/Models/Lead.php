<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Lead extends Model
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

    protected $fillable = [
        'name',
        'vehicle',
        'mobile',
        'area',
        'lead_source',
        'salesman',
        'notes',
        'language_type',
        'location_type',
        'created_by',
        'modified_by',
    ];

    protected $casts = [
        'vehicle' => 'array',
        'location_type' => 'array',
    ];

    protected $appends = ['vehicle_details', 'display_created_date', 'location_type_details', 'display_created_time'];

    public function getVehicleDetailsAttribute()
    {
        if (!empty($this->vehicle)) {
            $vehicleIds = is_array($this->vehicle) ? $this->vehicle : json_decode($this->vehicle, true);

            $vehicleNames = [];

            foreach ($vehicleIds as $id) {
                $vehicle = Vehicle::find($id);
                if ($vehicle) {
                    $vehicleNames[] = $vehicle->name;
                }
            }

            return implode(', ', $vehicleNames);
        }

        return '';
    }

    function getDisplayCreatedDateAttribute()
    {
        return Carbon::parse($this->created_at)->format('d-M-Y');
    }

    function getDisplayCreatedTimeAttribute()
    {
        return Carbon::parse($this->created_at)->format('h:i A');
    }

    public function getLocationTypeDetailsAttribute()
    {
        if (!empty($this->location_type)) {
            $locationTypeIds = is_array($this->location_type) ? $this->location_type : json_decode($this->location_type, true);
            
            $locationNames = [];
            foreach ($locationTypeIds as $id) {
                if ($id == 1) {
                    $locationNames[] = 'Sama Savli Road';
                } elseif ($id == 2) {
                    $locationNames[] = 'Kalali-Vadsar Road';
                }
            }
            
            return implode(', ', $locationNames);
        }

        return '';
    }
}
