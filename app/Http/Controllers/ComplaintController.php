<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ComplaintController extends Controller
{
    public function store(Request $request, Post $post)
    {
        try {
            $user = Auth::user();
            
            // Дополнительная проверка на фронтенд
            if ($post->user_ID === $user->id) {
                return response()->json([
                    'message' => 'Вы не можете жаловаться на собственное объявление'
                ], 422);
            }

            $post->addComplaint($user);
            
            return response()->json([
                'message' => 'Жалоба успешно отправлена',
                'complaints_count' => $post->complaint_number,
                'is_deleted' => $post->complaint_number >= 1
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}