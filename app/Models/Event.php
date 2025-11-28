<?php

namespace App\Models;

use Clerk\Backend\Models\Components\Status;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'status',
        'user_id',
        'company_id',
        'full_name',
        'whatsapp',
        'all_day',
        'notes',
        'start_time',
        'end_time',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
