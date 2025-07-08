<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassModel extends Model
{
    use HasFactory;

    protected $table = 'sch_classes';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'sch_token',
        'cl_key',
        'class',
        'stream',
        'class_teacher',
        'addby',
        'upby',
        'date',
        'upon'
    ];

    protected $casts = [
        'date' => 'datetime',
        'upon' => 'datetime'
    ];

    // Relationship with Stream
    public function stream()
    {
        return $this->belongsTo(Stream::class, 'stream', 'stream');
    }

    // Relationship with Students
    public function students()
    {
        return $this->hasMany(Student::class, 'stud_form', 'class');
    }
}