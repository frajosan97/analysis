<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $table = 'sch_students';
    protected $primaryKey = 'id';

    protected $fillable = [
        'sch_token',
        'stud_key',
        'stud_adm',
        'stud_status',
        'stud_cat',
        'stud_pass',
        'stud_gender',
        'stud_lname',
        'stud_fname',
        'stud_oname',
        'stud_form',
        'stud_stream',
        'stud_phone',
        'stud_house',
        'stud_kcpe_index',
        'stud_kcpe_marks',
        'stud_birth_date',
        'stud_birth_cert',
        'stud_county',
        'stud_drop_sub',
        'stud_password',
        'stud_reg_by',
        'stud_reg_on',
        'stud_up_by',
        'stud_up_on'
    ];

    protected $casts = [
        'stud_birth_date' => 'date',
        'stud_reg_on' => 'datetime',
        'stud_up_on' => 'datetime'
    ];

    // Relationship with Stream
    public function stream()
    {
        return $this->belongsTo(Stream::class, 'stud_stream', 'stream');
    }

    // Relationship with Class
    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'stud_form', 'class');
    }

    // Relationship with Results
    public function results()
    {
        return $this->hasMany(Result::class, 're_studK', 'stud_key');
    }
}
