<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    //fillable property for mass assignment
    protected $fillable = [
        'email', 'token','created_at','updated_at'
    ];
}
