<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemLogs extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'type',
        'type_id',
        'inquiry_id',
        'remark',
        'action_id',
        'created_by',
        'created_at',
        'updated_at'
    ];
}
