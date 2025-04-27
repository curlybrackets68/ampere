<?php

namespace App\Models;

use Carbon\Carbon;
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

    protected $appends = ['created_by_name', 'display_created_at'];


    public function getCreatedByNameAttribute()
    {
        $createdByName = "";

        $nameQuery = User::find($this->created_by);
        if ($nameQuery) {
            $createdByName = $nameQuery->user_name;
        }

        return $createdByName;
    }

    function getDisplayCreatedAtAttribute()
    {
        return Carbon::parse($this->created_at)->format('d-M-Y');
    }
}
