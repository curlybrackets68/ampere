<?php

namespace App\Models;

use App\Http\Controllers\CommonFunctions;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceDetail extends Model
{
    use HasFactory, CommonFunctions;

    protected $fillable = [
        'amc_id',
        'service_date',
        'service_remark',
        'attachment',
        'status',
    ];

    protected $appends = ['display_service_date', 'status_name'];

    function getDisplayServiceDateAttribute()
    {
        return Carbon::parse($this->service_date)->format('d-m-Y');
    }

    function getStatusNameAttribute()
    {
        return $this->statusArray[$this->status];
    }
}
