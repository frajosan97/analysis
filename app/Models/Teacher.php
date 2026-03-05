<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    protected $table = 'sch_users';

    protected $fillable = [
        'sch_token',
        'user_key',
        'user_role',
        'user_name',
        'user_pass',
        'user_gender',
        'user_salutation',
        'user_fname',
        'user_lname',
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    // user full name with salutation
    public function getFullNameAttribute()
    {
        return $this->user_salutation . ' ' . $this->user_lname;
    }
}
