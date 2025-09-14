<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model {
    use HasFactory;

    protected $fillable = ['title', 'description', 'course_category_id', 'feature_image', 'feature_video', 'price'];

    public function course_category() {
        return $this->belongsTo(CourseCategory::class);
    }

    public function modules() {
        return $this->hasMany(Module::class);
    }
}
