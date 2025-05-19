<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'login',
        'password',
        'phone_number',
        'birthday',
        'name',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_blocked' => 'boolean',
        'birthday' => 'date',
        'deleted_posts_count' => 'integer'
    ];

    public function blockIfNeeded()
    {
        if ($this->deleted_posts_count >= 5 && !$this->is_blocked) {
            $this->update(['is_blocked' => true]);
            return true;
        }
        return false;
    }

    public function posts()
    {
        return $this->hasMany(Post::class, 'user_ID');
    }

    public function complaints()
    {
        return $this->hasMany(Complaint::class);
    }

    public function getUserPosts()
    {
        return $this->posts()
            ->with(['category', 'foundStatus', 'district', 'postStatus'])
            ->orderBy('date', 'desc')
            ->paginate(10);
    }
}
