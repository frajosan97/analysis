<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stream extends Model
{
    use HasFactory;

    protected $table = 'sch_streams';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'sch_token',
        'str_key',
        'stream',
        'addby',
        'date'
    ];

    protected $casts = [
        'date' => 'datetime'
    ];

    // Relationship with Students
    public function students()
    {
        return $this->hasMany(Student::class, 'stud_stream', 'stream');
    }

    // Relationship with Classes
    public function classes()
    {
        return $this->hasMany(ClassModel::class, 'stream', 'stream');
    }
}