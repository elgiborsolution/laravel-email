<?php

namespace ESolution\LaravelEmail\Models;

use Illuminate\Database\Eloquent\Model;

class Suppression extends Model
{
    protected $table = 'le_suppressions';
    protected $fillable = ['email','reason'];
}
