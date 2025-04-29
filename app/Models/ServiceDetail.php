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
        'service_no',
        'amc_id',
        'service_date',
        'service_remark',
        'attachment',
        'status',
        'inquiry_flag',
        'inquiry_id'
    ];

    protected $appends = ['display_service_date', 'status_name','amc_master_details','attachment_url','service_by'];

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
        return User::find($this->modified_by)->user_name??'';
    }


    //amc_master_details
    public function getAmcMasterDetailsAttribute(){
        return AmcMaster::find($this->amc_id);
    }


}
