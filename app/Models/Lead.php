<?php

namespace App\Models;

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
        'created_by',
        'modified_by',
    ];

    protected $casts = [
        'vehicle' => 'array',
    ];

    protected $appends = ['vehicle_details'];

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
}
