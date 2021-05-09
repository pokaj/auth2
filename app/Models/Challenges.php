<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Challenges extends Model
{
    protected $table = 'challenge';

    protected $fillable = ['user_id', 'challenge'];

    protected $hidden = ['created_at', 'updated_at'];
}
