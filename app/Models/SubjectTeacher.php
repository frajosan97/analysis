<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubjectTeacher extends Model
{
    use HasFactory;

    protected $table = 'sch_tsub';

    protected $fillable = [
        'sch_token',
        'tsub_teacher',
        'tsub_form',
        'tsub_stream',
        'tsub_code',
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'tsub_teacher', 'user_key');
    }
}