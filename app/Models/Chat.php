<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Chat extends Model
{
    use HasFactory;

    use HasFactory;

    protected $fillable = ['type', 'uuid', 'name'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($chat) {
            $chat->uuid = Uuid::uuid4()->toString();
        });
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'chat_user');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany()->with('sender');
    }

    public function scopeIndividual($query)
    {
        return $query->where('type', 'INDIVIDUAL'); // Adjust 'INDIVIDUAL' if using an enum
    }

    public static function findChatBetweenUsers($userId1, $userId2)
    {
        return self::where('type', 'INDIVIDUAL')
            ->whereHas('users', function ($query) use ($userId1) {
                $query->where('user_id', $userId1);
            })
            ->whereHas('users', function ($query) use ($userId2) {
                $query->where('user_id', $userId2);
            })
            ->first();
    }
}
