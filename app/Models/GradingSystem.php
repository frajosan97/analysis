<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradingSystem extends Model
{
    use HasFactory;

    protected $table = 'sch_grading_system';

    protected $fillable = [
        'sch_token',
        'grds_key',
        'grds_cat_key',
        'grds_grade',
        'grds_min',
        'grds_max',
        'grds_point',
        'grds_rem',
        'grds_lugha',
        'addby',
        'upby',
    ];

    protected $casts = [
        'date' => 'datetime',
        'grds_min' => 'decimal:2',
        'grds_max' => 'decimal:2',
    ];

    /**
     * Get the grading category this system belongs to
     */
    public function gradingCategory()
    {
        return $this->belongsTo(GradingCat::class, 'grds_cat_key', 'grd_key');
    }

    /**
     * Get the school subject associated with this grading system
     */
    public function schoolSubject()
    {
        return $this->belongsTo(Subject::class, 'sch_token', 'sch_token');
    }
}
