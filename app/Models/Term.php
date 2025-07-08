<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Term extends Model
{
    use HasFactory;

    protected $table = 'sch_term';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'sch_token',
        'term_key',
        'term',
        'start_date',
        'end_date',
        'term_status',
        'addby',
        'date'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'date' => 'datetime'
    ];

    // Relationship with Exams
    public function exams()
    {
        return $this->hasMany(Exam::class, 'exam_term', 'term_key');
    }

    // Relationship with Results
    public function results()
    {
        return $this->hasMany(Result::class, 're_term', 'term_key');
    }
}