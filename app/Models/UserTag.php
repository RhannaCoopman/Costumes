<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTag extends Model
{
    use HasFactory;

    protected $table = 'user_tags';

    protected $fillable = [
        'user_id',
        'tag_id',
        'score'
    ];

    /**
     * Get the user associated with this UserTag.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the tag associated with this UserTag.
     */
    public function tag()
    {
        return $this->belongsTo(Tag::class);
    }
}
