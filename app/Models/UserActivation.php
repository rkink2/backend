<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserActivation extends Model
{
    protected $fillable = [
        'user_id','token'
    ];
    protected $table = "user_activations";

    protected $hidden = [
    ];
}
