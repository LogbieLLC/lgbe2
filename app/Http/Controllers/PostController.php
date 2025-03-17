<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PostController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index', 'show']);
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index(Community $community)
    {
        $posts = $community->posts()
            ->with(['user', 'community'])
            ->withCount(['comments', 'votes'])
            ->orderByDesc('created_at')
            ->paginate(15);
            
        return response()->json($posts);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Community $community)
    {
        // Check if user is a member of the community
        $isMember = $community->members()->where('user_id', Auth::id())->exists();
        
        if (!$isMember) {
            return redirect()->route('communities.show', $community)
                ->with('error', 'You must be a member of the community to create a post.');
        }
        
        return Inertia::render('Posts/Create', [
            'community' => $community
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Community $community)
    {
        // Check if user is a member of the community
        $isMember = $community->members()->where('user_id', Auth::id())->exists();
        
        if (!$isMember) {
            return response()->json(['message' => 'You must be a member of the community to create a post.'], 403);
        }
        
        $validated = $request->validate([
            'title' => 'required|string|max:300',
            'content' => 'required|string|max:40000',
            'type' => 'required|in:text,link,image,video',
        ]);
        
        $post = $community->posts()->create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'type' => $validated['type'],
            'user_id' => Auth::id(),
        ]);
        
        if ($request->wantsJson()) {
            return response()->json($post, 201);
        }
        
        return redirect()->route('posts.show', $post)
            ->with('success', 'Post created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        $post->load(['user', 'community']);
        
        $comments = $post->comments()
            ->with(['user'])
            ->whereNull('parent_comment_id')
            ->withCount(['votes', 'replies'])
            ->orderByDesc('created_at')
            ->paginate(15);
            
        $userVote = null;
        
        if (Auth::check()) {
            $userVote = $post->votes()->where('user_id', Auth::id())->first();
        }
        
        return Inertia::render('Posts/Show', [
            'post' => $post,
            'comments' => $comments,
            'userVote' => $userVote ? $userVote->vote_type : null,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        $this->authorize('update', $post);
        
        return Inertia::render('Posts/Edit', [
            'post' => $post,
            'community' => $post->community,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        $this->authorize('update', $post);
        
        $validated = $request->validate([
            'title' => 'required|string|max:300',
            'content' => 'required|string|max:40000',
        ]);
        
        $post->update($validated);
        
        if ($request->wantsJson()) {
            return response()->json($post);
        }
        
        return redirect()->route('posts.show', $post)
            ->with('success', 'Post updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);
        
        $community = $post->community;
        $post->delete();
        
        if (request()->wantsJson()) {
            return response()->json(['message' => 'Post deleted successfully']);
        }
        
        return redirect()->route('communities.show', $community)
            ->with('success', 'Post deleted successfully!');
    }
}
