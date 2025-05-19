<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PostController extends Controller
{
    use AuthorizesRequests;
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

    public function update(Request $request, Post $post)
    {
        $this->authorize('update', $post);

        try {
            $validatedData = Post::validateUpdate($request->all());
            $updatedPost = $post->updatePost($validatedData, $request->file('photo'));

            return response()->json([
                'message' => 'Post updated successfully',
                'post' => $updatedPost
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Post update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);

        try {
            $post->deletePost();
            return response()->json(['message' => 'Post deleted successfully']);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Post deletion failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function userPosts(Request $request)
    {
        $user = $request->user();
        $posts = $user->getUserPosts();
        
        return response()->json([
            'posts' => $posts,
            'user' => [
                'name' => $user->name,
                'total_posts' => $user->posts()->count(),
                'deleted_posts' => $user->deleted_posts_count
            ]
        ]);
    }

    public function index(Request $request)
    {
        $posts = Post::filter($request->all());
        return response()->json($posts);
    }

    public function search(Request $request)
    {
        $request->validate(['query' => 'required|string|min:2']);
        
        $posts = Post::search($request->input('query')); 
        return response()->json($posts);
    }
}