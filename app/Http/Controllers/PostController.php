<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function store(Request $request)
    {
        $user = Auth::user();
        
        try {
            $validatedData = Post::validateCreation($request->all());
            
            $post = Post::createPost(
                $validatedData,
                $user,
                $request->file('photo')
            );

            return response()->json([
                'message' => 'Post created successfully',
                'post' => $post->load(['category', 'foundStatus', 'postStatus', 'district'])
            ], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Post creation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}