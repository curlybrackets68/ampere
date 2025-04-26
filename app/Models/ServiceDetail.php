<?php

namespace App\Models;

use App\Http\Controllers\CommonFunctions;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceDetail extends Model
{
    use HasFactory,SoftDeletes,CommonFunctions;

    protected $fillable = [
        'amc_id',
        'service_date',
        'service_remark',
        'attachment',
        'status',
    ];

    protected $appends = ['display_service_date', 'status_name','amc_master_details','attachment_url'];

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
        return ;
    }


    //amc_master_details
    public function getAmcMasterDetailsAttribute(){
        return AmcMaster::find($this->amc_id);
    }


}
