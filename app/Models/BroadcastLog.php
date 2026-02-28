<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BroadcastLog extends Model
{
    protected $fillable = [
        'sent_by',
        'message',
        'dealer_count',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }
}
