<?php

namespace ESolution\LaravelEmail\Models;

use Illuminate\Database\Eloquent\Model;

class BroadcastRecipient extends Model
{
    protected $table = 'le_broadcast_recipients';
    protected $fillable = ['broadcast_id','email','name','token','sent_at','provider_message_id','opened_at','unsubscribed_at','bounced_at'];
    protected $casts = [
        'sent_at'=>'datetime','opened_at'=>'datetime','unsubscribed_at'=>'datetime','bounced_at'=>'datetime'
    ];
}
