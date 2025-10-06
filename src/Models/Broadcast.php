<?php

namespace ESolution\LaravelEmail\Models;

use Illuminate\Database\Eloquent\Model;

class Broadcast extends Model
{
    protected $table = 'le_broadcasts';
    protected $fillable = ['name','template_id','provider_key','status','headers','custom_args'];
    protected $casts = ['headers'=>'array','custom_args'=>'array'];
}
