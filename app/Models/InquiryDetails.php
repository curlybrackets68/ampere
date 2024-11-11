<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InquiryDetails extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'inquiry_no',
        'mobile',
        'vehicle_no',
        'service_type_id',
        'branch_id',
        'status_id',
        'inquiry_date',
        'confirm_date',
        'address',
        'created_by',
        'modified_by',
        'created_at',
        'updated_at',
        'status_remark'
    ];

}
