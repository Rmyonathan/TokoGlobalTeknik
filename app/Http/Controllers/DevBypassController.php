<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class DevBypassController extends Controller
{
    /**
     * Show bypass login page (development only)
     */
    public function index()
    {
        // Only allow in development
        if (!app()->environment('local') && !config('app.debug')) {
            abort(404);
        }
        
        $users = User::select('id', 'name', 'email', 'role')->get();
        
        return view('dev.bypass', compact('users'));
    }
    
    /**
     * Quick login bypass
     */
    public function login(Request $request)
    {
        // Only allow in development
        if (!app()->environment('local') && !config('app.debug')) {
            abort(404);
        }
        
        $userId = $request->input('user_id');
        $userEmail = $request->input('user_email');
        
        $user = null;
        
        if ($userId) {
            $user = User::find($userId);
        } elseif ($userEmail) {
            $user = User::where('email', $userEmail)->first();
        }
        
        if ($user) {
            Auth::login($user);
            
            \Log::info("Development bypass login: {$user->name} ({$user->email})");
            
            return redirect('/')->with('success', "Logged in as {$user->name}");
        }
        
        return redirect()->back()->with('error', 'User not found');
    }
    
    /**
     * Quick role-based login
     */
    public function quickLogin($role)
    {
        // Only allow in development
        if (!app()->environment('local') && !config('app.debug')) {
            abort(404);
        }
        
        $user = User::where('role', $role)->first();
        
        if ($user) {
            Auth::login($user);
            return redirect('/')->with('success', "Logged in as {$user->name} ({$role})");
        }
        
        return redirect('/login')->with('error', "No user found with role: {$role}");
    }
}
