<?php

namespace App\Models;

use App\Http\Controllers\CommonFunctions;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceDetail extends Model
{
    use HasFactory, SoftDeletes, CommonFunctions;

    protected $fillable = [
        'service_no',
        'amc_id',
        'service_date',
        'service_km',
        'service_remark',
        'attachment',
        'status',
        'inquiry_flag',
        'inquiry_id',
        'service_type',
        'reminder_days',
    ];

    protected $appends = ['display_service_date', 'status_name', 'amc_master_details', 'attachment_url', 'service_by', 'service_type_text'];

    public function amcMaster()
    {
        return $this->belongsTo(AmcMaster::class, 'amc_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }

    function getDisplayServiceDateAttribute()
    {
        return Carbon::parse($this->service_date)->format('d-M-Y');
    }

    function getStatusNameAttribute()
    {
        return $this->statusArray[$this->status];
    }
    function getAttachmentUrlAttribute()
    {
        $path = public_path('assets/attachment/amc-master/' . $this->amc_id . '/service/' . $this->attachment);

        if (!empty($this->attachment) && file_exists($path)) {
            return asset('assets/attachment/amc-master/' . $this->amc_id . '/service/' . $this->attachment);
        }

        return '';
    }
    function getServiceByAttribute()
    {
        return $this->user->user_name ?? (User::find($this->modified_by)->user_name ?? '');
    }

    //amc_master_details
    public function getAmcMasterDetailsAttribute()
    {
        return $this->amcMaster ?? AmcMaster::find($this->amc_id);
    }

    //service_type_text
    function getServiceTypeTextAttribute()
    {
        return $this->amcServiceType[$this->service_type];
    }
}
