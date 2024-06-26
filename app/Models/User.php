<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['name', 'email', 'password'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function likedPosts()
    {
        return $this->belongsToMany(Post::class, 'likes')
        ->withTimestamps();
    }

    public function savedPosts()
    {
        return $this->belongsToMany(Post::class, 'saves')
            ->withTimestamps()
            ->orderBy('saves.created_at', 'desc');
    }

    /**
     * Get the posts uploaded by the user.
     */
    public function uploadedPosts()
    {
        return $this->hasMany(Post::class)->orderBy('updated_at', 'desc');;
    }

    /**
     * Get the tags associated with the user.
     */
    public function userTags()
    {
        return $this->hasMany(UserTag::class);
    }

    /**
     * Get the tags associated with the user through the userTags relationship.
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'user_tags')->withPivot('score');
    }
}
