<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'class_id',
        'gender',
        'date_of_birth',
        'address'
    ];

    // Relationship to ClassRoom
    public function class()
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }
}
