<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AmcPackageMaster extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_type',
        'service_count',
        'duration',
        'time_period',
        'created_by',
        'modified_by',
    ];

}
