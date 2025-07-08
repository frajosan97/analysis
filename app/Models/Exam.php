<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;

    protected $table = 'sch_exam';
    protected $primaryKey = 'id';

    protected $fillable = [
        'sch_token',
        'exam_key',
        'exam_term',
        'exam',
        'start_date',
        'end_date',
        'exam_status',
        'addby',
        'date'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'date' => 'datetime'
    ];

    // Relationship with Term
    public function term()
    {
        return $this->belongsTo(Term::class, 'exam_term', 'term_key');
    }

    // Relationship with Results
    public function results()
    {
        return $this->hasMany(Result::class, 're_exam', 'exam_key');
    }
}
