<?php

namespace ESolution\LaravelEmail\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $table = 'le_email_templates';
    protected $fillable = ['name','subject','html','text','from_email','from_name'];
}
