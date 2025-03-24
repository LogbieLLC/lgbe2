<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class SettingsController extends Controller
{
    /**
     * Show the profile settings page.
     */
    public function showProfile()
    {
        return response()->json([
            'user' => Auth::user()
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        // Check if user is a super admin and trying to modify super admin status
        if ($user->is_super_admin && $request->has('is_super_admin') && !$request->is_super_admin) {
            return back()->withErrors(['is_super_admin' => 'Super admin status cannot be modified']);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ]);

        if ($request->email !== $user->email) {
            $validated['email_verified_at'] = null;
        }

        $user->update($validated);

        // Check if this is a web request or API request
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Profile updated successfully',
                'user' => $user
            ]);
        }
        
        return redirect('/settings/profile');
    }

    /**
     * Show the password settings page.
     */
    public function showPassword()
    {
        return response()->json([
            'user' => Auth::user()
        ]);
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        // Check if this is a web request or API request
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Password updated successfully'
            ]);
        }
        
        return redirect('/settings/password');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
