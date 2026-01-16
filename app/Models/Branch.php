<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

    class Branch extends Model
{
    protected $fillable = ['name', 'code', 'address'];

    public function courses() {
        return $this->hasMany(Course::class);
    }

    public function students() {
        return $this->hasMany(Student::class);
    }
}


