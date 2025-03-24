<?php

namespace App\Http\Controllers;

use App\Models\Community;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CommunityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $communities = Community::withCount('members')
            ->orderBy('members_count', 'desc')
            ->paginate(20);

        if ($request->expectsJson()) {
            return response()->json([
                'data' => $communities->items(),
                'links' => [
                    'prev_page_url' => $communities->previousPageUrl(),
                    'next_page_url' => $communities->nextPageUrl()
                ],
                'meta' => [
                    'current_page' => $communities->currentPage(),
                    'last_page' => $communities->lastPage(),
                    'per_page' => $communities->perPage(),
                    'total' => $communities->total()
                ]
            ]);
        }

        return Inertia::render('Communities/Index', [
            'communities' => [
                'data' => $communities->items(),
                'prev_page_url' => $communities->previousPageUrl(),
                'next_page_url' => $communities->nextPageUrl()
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Communities/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|min:3|max:255|unique:communities',
            'description' => 'required|string|max:1000',
            'rules' => 'nullable|string|max:5000',
        ]);

        $community = Community::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'rules' => $validated['rules'] ?? '',
            'created_by' => Auth::id(),
        ]);

        // Add the creator as a moderator
        $community->members()->attach(Auth::id(), ['role' => 'moderator']);

        if ($request->expectsJson()) {
            return response()->json($community, 201);
        }

        return redirect()->route('communities.show', $community)
            ->with('success', 'Community created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Community $community)
    {
        $community->load(['creator', 'moderators']);

        $posts = $community->posts()
            ->with(['user', 'community'])
            ->withCount(['comments', 'votes'])
            ->orderByDesc('created_at')
            ->paginate(15);

        $isMember = false;
        $isModerator = false;

        if (Auth::check()) {
            $membership = $community->members()->where('user_id', Auth::id())->first();
            $isMember = $membership !== null;
            $isModerator = $membership && $membership->pivot->role === 'moderator';
        }

        return Inertia::render('Communities/Show', [
            'community' => $community,
            'posts' => $posts,
            'isMember' => $isMember,
            'isModerator' => $isModerator,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Community $community)
    {
        if (Auth::id() !== $community->created_by && !$community->moderators()->where('user_id', Auth::id())->exists()) {
            abort(403, 'Unauthorized action');
        }

        return Inertia::render('Communities/Edit', [
            'community' => $community,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Community $community)
    {
        if (Auth::id() !== $community->created_by && !$community->moderators()->where('user_id', Auth::id())->exists()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized action'], 403);
            }
            abort(403, 'Unauthorized action');
        }

        $validated = $request->validate([
            'description' => 'required|string|max:1000',
            'rules' => 'nullable|string|max:5000',
        ]);

        $community->update($validated);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Community updated successfully']);
        }

        return redirect()->route('communities.show', $community)
            ->with('success', 'Community updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Community $community)
    {
        if (Auth::id() !== $community->created_by) {
            if (request()->expectsJson()) {
                return response()->json(['message' => 'Unauthorized action'], 403);
            }
            abort(403, 'Unauthorized action');
        }

        $community->delete();

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Community deleted successfully']);
        }

        return redirect()->route('communities.index')
            ->with('success', 'Community deleted successfully!');
    }
    
    /**
     * Remove a post from a community.
     */
    public function removePost(Community $community, \App\Models\Post $post)
    {
        // Check if user is a moderator
        if (!$community->moderators()->where('user_id', Auth::id())->exists()) {
            return response()->json(['message' => 'Unauthorized action'], 403);
        }

        // Check if post belongs to the community
        if ($post->community_id !== $community->id) {
            return response()->json(['message' => 'Post does not belong to this community'], 404);
        }

        // Soft delete the post
        $post->delete();

        return response()->json(['message' => 'Post removed successfully']);
    }

    /**
     * Join a community.
     */
    public function join(Community $community)
    {
        // Check if user is already a member
        if (!$community->members()->where('user_id', Auth::id())->exists()) {
            $community->members()->attach(Auth::id(), ['role' => 'member']);
            return response()->json(['message' => 'Successfully joined community']);
        }

        return response()->json(['message' => 'Already a member of this community'], 400);
    }

    /**
     * Leave a community.
     */
    public function leave(Community $community)
    {
        // Check if user is a member
        if ($community->members()->where('user_id', Auth::id())->exists()) {
            // Don't allow the creator to leave
            if ($community->created_by === Auth::id()) {
                return response()->json(['message' => 'Community creator cannot leave'], 403);
            }

            $community->members()->detach(Auth::id());
            return response()->json(['message' => 'Left community successfully']);
        }

        return response()->json(['message' => 'Not a member of this community'], 409);
    }
    
    /**
     * Get posts for a community.
     */
    public function posts(Community $community)
    {
        // Get posts with vote counts
        $posts = $community->posts()
            ->with(['user', 'community'])
            ->withCount(['comments', 'votes'])
            ->get();
            
        // Sort by score (upvotes - downvotes)
        $sortedPosts = $posts->sortByDesc(function ($post) {
            $upvotes = $post->votes()->where('vote_type', 'up')->count();
            $downvotes = $post->votes()->where('vote_type', 'down')->count();
            return $upvotes - $downvotes;
        })->values()->take(15);
            
        return response()->json([
            'data' => $sortedPosts,
            'meta' => [
                'total' => $posts->count()
            ]
        ]);
    }
}
