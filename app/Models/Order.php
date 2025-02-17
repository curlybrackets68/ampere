<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Order extends Model
{
    use HasFactory;


   

    protected $fillable = [
        'order_no',
        'customer_name',
        'customer_mobile',
        'customer_vehicle_no',
        'order_name',
        'order_date',
        'status_id',
        'status_date',
        'created_by',
        'modified_by'
    ];
}
