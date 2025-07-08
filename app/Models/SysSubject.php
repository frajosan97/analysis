<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SysSubject extends Model
{
    use HasFactory;

    protected $table = 'sys_subjects';

    protected $fillable = [
        'sub_code',
        'sub_cat',
        'sub_name',
        'sub_short_name',
        'sub_group',
        'addby',
        'upby',
    ];

    protected $casts = [
        'date' => 'datetime',
        'sub_code' => 'integer',
    ];

    /**
     * Get all school subjects that use this system subject
     */
    public function schoolSubjects()
    {
        return $this->hasMany(Subject::class, 'sch_sub_code', 'sub_code');
    }
}