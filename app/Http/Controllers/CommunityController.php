<?php

namespace App\Http\Controllers;

use App\Models\Community;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CommunityController extends Controller
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
    public function index()
    {
        $communities = Community::withCount('members')
            ->orderBy('members_count', 'desc')
            ->paginate(20);
            
        return Inertia::render('Communities/Index', [
            'communities' => $communities
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
            'name' => 'required|string|max:255|unique:communities',
            'description' => 'required|string|max:1000',
            'rules' => 'nullable|string|max:5000',
        ]);
        
        $community = Community::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'rules' => $validated['rules'],
            'created_by' => Auth::id(),
        ]);
        
        // Add the creator as a moderator
        $community->members()->attach(Auth::id(), ['role' => 'moderator']);
        
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
        $this->authorize('update', $community);
        
        return Inertia::render('Communities/Edit', [
            'community' => $community,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Community $community)
    {
        $this->authorize('update', $community);
        
        $validated = $request->validate([
            'description' => 'required|string|max:1000',
            'rules' => 'nullable|string|max:5000',
        ]);
        
        $community->update($validated);
        
        return redirect()->route('communities.show', $community)
            ->with('success', 'Community updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Community $community)
    {
        $this->authorize('delete', $community);
        
        $community->delete();
        
        return redirect()->route('communities.index')
            ->with('success', 'Community deleted successfully!');
    }
    
    /**
     * Join a community.
     */
    public function join(Community $community)
    {
        // Check if user is already a member
        if (!$community->members()->where('user_id', Auth::id())->exists()) {
            $community->members()->attach(Auth::id(), ['role' => 'member']);
            return response()->json(['message' => 'Joined community successfully']);
        }
        
        return response()->json(['message' => 'Already a member of this community'], 409);
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
}
