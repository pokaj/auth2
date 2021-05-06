<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthCodes extends Model
{
    protected $table = 'auth_codes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['auth_code', 'expired'];


    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at', 
    ];

}
