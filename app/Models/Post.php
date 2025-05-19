<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
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

    public static function getFullPostById($id)
    {
        return self::with([
            'category',
            'foundStatus',
            'postStatus',
            'district',
            'user.contacts'
        ])->find($id);
    }

    public static function validateUpdate(array $data)
    {
        return validator($data, [
            'title' => 'sometimes|string|max:100',
            'description' => 'sometimes|string',
            'category_ID' => 'sometimes|integer|exists:categories,ID',
            'photo' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            'found_ID' => 'sometimes|integer|exists:found,ID',
            'street' => 'nullable|string|max:100',
            'house' => 'nullable|string|max:20',
            'district_ID' => 'sometimes|integer|exists:districts,ID',
        ])->validate();
    }

    public function updatePost(array $data, $photo = null)
    {
        if ($photo) {
            Storage::disk('public')->delete($this->photo);
            $this->photo = self::storePhoto($photo);
        }

        $this->fill($data);
        $this->save();

        return $this->fresh()->load(['category', 'foundStatus', 'postStatus', 'district']);
    }

    public function deletePost()
    {
        if ($this->photo) {
            Storage::disk('public')->delete($this->photo);
        }

        return $this->delete();
    }

    protected static function storePhoto($photo)
    {
        $extension = $photo->getClientOriginalExtension();
        $filename = Str::random(40) . '.' . $extension;
        $path = $photo->storeAs('posts', $filename, 'public');

        return $path;
    }

    public static function filter(array $filters)
    {
        return self::query()
            ->when(isset($filters['district_ID']), fn($q) => $q->where('district_ID', $filters['district_ID']))
            ->when(isset($filters['found_ID']), fn($q) => $q->where('found_ID', $filters['found_ID']))
            ->when(isset($filters['category_ID']), fn($q) => $q->where('category_ID', $filters['category_ID']))
            ->with(['category', 'foundStatus', 'district', 'user'])
            ->latest()
            ->paginate(15);
    }

    public static function search(string $query)
    {
        return self::query()
            ->where(function($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                ->orWhere('description', 'like', "%{$query}%");
            })
            ->with(['category', 'foundStatus', 'district'])
            ->latest()
            ->paginate(15);
    }

    public function complaints()
    {
        return $this->hasMany(Complaint::class);
    }

    public function addComplaint(User $complainingUser)
    {
        DB::transaction(function () use ($complainingUser) {
            // Проверка, что пользователь не автор
            if ($this->user_ID === $complainingUser->id) {
                throw new \Exception('Нельзя жаловаться на своё объявление');
            }

            // Проверка существующей жалобы
            if ($this->complaints()->where('user_id', $complainingUser->id)->exists()) {
                throw new \Exception('Вы уже жаловались на это объявление');
            }

            // Создаём жалобу
            $this->complaints()->create(['user_id' => $complainingUser->id]);
            $this->increment('complaint_number');

            // Проверка на блокировку
            if ($this->complaint_number >= 10) {
                // Атомарное обновление с получением обновлённой модели
                $author = User::where('id', $this->user_ID)
                    ->lockForUpdate()
                    ->first();

                $author->increment('deleted_posts_count');
                $this->delete();

                if ($author->deleted_posts_count >= 5) {
                    $author->is_blocked = true;
                    $author->save();

                    \Log::info('User blocked successfully', [
                        'user_id' => $author->id,
                        'deleted_posts' => $author->deleted_posts_count,
                        'is_blocked' => $author->is_blocked
                    ]);
                }
            }
        });
    }
}
