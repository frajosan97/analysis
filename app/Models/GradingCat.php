<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradingCat extends Model
{
    use HasFactory;

    protected $table = 'sch_grading_cat';

    protected $fillable = [
        'sch_token',
        'grd_key',
        'grd_subcat',
        'addby',
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    /**
     * Get the school subject associated with this grading category
     */
    public function schoolSubject()
    {
        return $this->belongsTo(Subject::class, 'sch_token', 'sch_token');
    }

    /**
     * Get the grading systems for this category
     */
    public function gradingSystems()
    {
        return $this->hasMany(GradingSystem::class, 'grds_cat_key', 'grd_key');
    }
}
