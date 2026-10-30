<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    public function requirements(): HasMany
{
    return $this->hasMany(Requirement::class);
}
    protected $fillable = [
        'student_number',
        'first_name',
        'last_name',
        'email',
        'course',
        'year_level',
        'section',
    ];
}