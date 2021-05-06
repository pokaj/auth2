<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table = 'companies';

    protected $fillable = ['name', 'country', 'contact_email', 'account_balance'];

    protected $hidden = [
        'created_at', 'updated_at'
    ];

}
