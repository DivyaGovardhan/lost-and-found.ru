<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_ID',
        'title',
        'description',
        'category_ID',
        'photo',
        'found_ID',
        'status_ID',
        'street',
        'house',
        'district_ID'
    ];

    public $timestamps = false;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($post) {
            $post->status_ID = $post->status_ID ?? 1; // По умолчанию "Активно"
            $post->date = now();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_ID');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_ID');
    }

    public function foundStatus()
    {
        return $this->belongsTo(Found::class, 'found_ID');
    }

    public function postStatus()
    {
        return $this->belongsTo(Status::class, 'status_ID');
    }

    public function district()
    {
        return $this->belongsTo(District::class, 'district_ID');
    }

    public static function validateCreation(array $data)
    {
        return validator($data, [
            'title' => 'required|string|max:100',
            'description' => 'required|string',
            'category_ID' => 'required|integer|exists:categories,ID',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'found_ID' => 'required|integer|exists:found,ID',
            'street' => 'nullable|string|max:100',
            'house' => 'nullable|string|max:20',
            'district_ID' => 'required|integer|exists:districts,ID',
        ])->validate();
    }

    public static function createPost(array $data, $user, $photo = null)
    {
        $photoPath = $photo ? self::storePhoto($photo) : null;

        return self::create([
            'user_ID' => $user->id,
            'title' => $data['title'],
            'description' => $data['description'],
            'category_ID' => $data['category_ID'],
            'photo' => $photoPath,
            'found_ID' => $data['found_ID'],
            'street' => $data['street'] ?? null,
            'house' => $data['house'] ?? null,
            'district_ID' => $data['district_ID'],
        ]);
    }

    protected static function storePhoto($photo)
    {
        $extension = $photo->getClientOriginalExtension();
        $filename = Str::random(40) . '.' . $extension;
        $path = $photo->storeAs('posts', $filename, 'public');
        
        return $path;
    }
}