<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'role';

    protected $fillable = ['name'];

    protected $hidden = [
        'created_at', 'updated_at'
    ];


}
