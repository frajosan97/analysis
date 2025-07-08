<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $table = 'sch_subjects';

    protected $fillable = [
        'sch_token',
        'sch_sub_code',
        'sch_sub_comp',
        'addby',
        'upby',
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    /**
     * Get the system subject associated with this school subject
     */
    public function systemSubject()
    {
        return $this->belongsTo(SysSubject::class, 'sch_sub_code', 'sub_code');
    }

    /**
     * Get the grading categories for this subject
     */
    public function gradingCategories()
    {
        return $this->hasMany(GradingCat::class, 'sch_token', 'sch_token');
    }
}