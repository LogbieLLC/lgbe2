<?php

namespace App\Http\Controllers;

use App\Models\Ban;
use App\Models\Community;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BanController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index(Community $community)
    {
        $this->authorize('moderate', $community);
        
        $bans = $community->bans()
            ->with(['user', 'moderator'])
            ->paginate(20);
            
        return response()->json($bans);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Community $community, User $user)
    {
        $this->authorize('moderate', $community);
        
        // Check if the user is already banned
        $existingBan = $community->bans()->where('user_id', $user->id)->first();
        if ($existingBan) {
            return response()->json(['message' => 'User is already banned from this community'], 409);
        }
        
        // Check if trying to ban a moderator
        $isModerator = $community->moderators()->where('user_id', $user->id)->exists();
        if ($isModerator) {
            return response()->json(['message' => 'Cannot ban a moderator'], 403);
        }
        
        // Check if trying to ban the community creator
        if ($community->created_by === $user->id) {
            return response()->json(['message' => 'Cannot ban the community creator'], 403);
        }
        
        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
            'expires_at' => 'nullable|date|after:now',
        ]);
        
        $ban = $community->bans()->create([
            'user_id' => $user->id,
            'banned_by' => Auth::id(),
            'reason' => $validated['reason'],
            'expires_at' => $validated['expires_at'] ?? null,
        ]);
        
        // Remove user from community members
        $community->members()->detach($user->id);
        
        return response()->json($ban, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Community $community, Ban $ban)
    {
        $this->authorize('moderate', $community);
        
        if ($ban->community_id !== $community->id) {
            return response()->json(['message' => 'Ban does not belong to this community'], 404);
        }
        
        $ban->load(['user', 'moderator']);
        
        return response()->json($ban);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Community $community, Ban $ban)
    {
        $this->authorize('moderate', $community);
        
        if ($ban->community_id !== $community->id) {
            return response()->json(['message' => 'Ban does not belong to this community'], 404);
        }
        
        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
            'expires_at' => 'nullable|date|after:now',
        ]);
        
        $ban->update($validated);
        
        return response()->json($ban);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Community $community, User $user)
    {
        $this->authorize('moderate', $community);
        
        $ban = $community->bans()->where('user_id', $user->id)->first();
        
        if (!$ban) {
            return response()->json(['message' => 'User is not banned from this community'], 404);
        }
        
        $ban->delete();
        
        return response()->json(['message' => 'Ban removed successfully']);
    }
}
