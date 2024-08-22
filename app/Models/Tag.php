<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Tag extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'name',
        'id'
    ];

    public function posts()
    {
        return $this->belongsToMany(Post::class, 'post_tags');
    }

    /**
     * Get the users associated with the tag.
     */
    public function userTags()
    {
        return $this->hasMany(UserTag::class);
    }

    /**
     * Get the users associated with the tag through the userTags relationship.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_tags')->withPivot('score');
    }
}
