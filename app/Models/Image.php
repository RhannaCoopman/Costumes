<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;

    protected $fillable = ['filename', 'mime', 'path', 'size'];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function annotations()
    {
        return $this->hasMany(Annotation::class);
    }
}
