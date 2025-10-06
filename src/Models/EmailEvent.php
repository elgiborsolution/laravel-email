<?php

namespace ESolution\LaravelEmail\Models;

use Illuminate\Database\Eloquent\Model;

class EmailEvent extends Model
{
    protected $table = 'le_email_events';
    protected $fillable = ['broadcast_id','recipient_id','event','provider','payload'];
    protected $casts = ['payload'=>'array'];
}
