<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceDetail extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'amc_id',
        'service_date',
        'service_remark',
        'attachment',
        'status',
    ];
}
