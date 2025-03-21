<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProfileController extends Controller
{
    /**
     * Display the user's profile.
     */
    public function show(User $user)
    {
        $posts = $user->posts()
            ->with(['community'])
            ->withCount(['comments', 'votes'])
            ->orderByDesc('created_at')
            ->paginate(10);
            
        $comments = $user->comments()
            ->with(['post', 'post.community'])
            ->withCount('votes')
            ->orderByDesc('created_at')
            ->paginate(10);
            
        $communities = $user->communities()
            ->withCount('members')
            ->orderByDesc('members_count')
            ->get();
            
        $moderatedCommunities = $user->moderatedCommunities()
            ->withCount('members')
            ->orderByDesc('members_count')
            ->get();
            
        return Inertia::render('Profile/Show', [
            'profileUser' => $user,
            'posts' => $posts,
            'comments' => $comments,
            'communities' => $communities,
            'moderatedCommunities' => $moderatedCommunities,
            'isCurrentUser' => Auth::id() === $user->id,
        ]);
    }
    
    /**
     * Edit the user's profile.
     */
    public function edit()
    {
        return Inertia::render('Profile/Edit', [
            'user' => Auth::user(),
        ]);
    }
    
    /**
     * Update the user's profile.
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ]);
        
        $user->update($validated);
        
        return redirect()->route('profile.show', $user)
            ->with('success', 'Profile updated successfully!');
    }
    
    /**
     * Delete the user's account.
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'password' => 'required|current_password',
        ]);
        
        $user = Auth::user();
        
        // Prevent deletion of super admin users
        if ($user->is_super_admin) {
            return back()->withErrors([
                'delete' => 'Super admin accounts cannot be deleted through this interface.'
            ]);
        }
        
        Auth::logout();
        
        $user->delete();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/');
    }
}
