<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;


class AccountsController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function accountMaintenance(Request $request)
    {
        $user = Auth::user();

        $users = User::all();

        if ($user->role === 'admin') {
            return view('account-maintenance', [
                'users' => $users,
            ]);
        }

        return abort(403, 'Unauthorized action.');
    }

    public function createRole()
    {
        $permissions = Permission::all();
        return view('addRole', compact('permissions'));
    }

    public function storeRole(Request $request)
    {
        $role = Role::create(['name' => $request->name]);
        $role->syncPermissions($request->permissions); // array of permission IDs
        return redirect()->route('createRole');
    }

    public function switchDatabase(Request $request)
    {
        $database = $request->input('database');

        // Cek apakah database yang dipilih valid
        if (array_key_exists($database, config('database.available_databases'))) {
            // Invalidate current session
            // session()->forget(['selected_database']);

            // // Store new database in session
            // session()->put('selected_database', $database);
            // session()->put('auth_user_id', Auth::id()); // Simpan user ID manual
            // session()->save();

            // session()->forget('selected_database');
            // session(['selected_database' => $database]);
            // session()->save(); // Paksa Laravel menyimpan session
            session()->put('selected_database', $database);
            session()->save(); // Pastikan session tersimpan
            session()->reflash(); // Simpan session agar tetap ada di request berikutnya


            // Dynamically update database connection
            $newDatabase = config("database.available_databases");
            // config(['database.connections.mariadb.database' => $newDatabase]);
            $selectedDatabase = session('selected_database');

            // if ($selectedDatabase && array_key_exists($selectedDatabase, config('database.available_databases'))) {
            //     Config::set('database.connections.mariadb.database', config("database.available_databases.$selectedDatabase"));

            //     // Paksa update cache konfigurasi
            //     config(['database.connections.mariadb.database' => config("database.available_databases.$selectedDatabase")]);

            //     app('db')->purge('mariadb');
            //     app('db')->reconnect('mariadb');
            // }

            Config::set('database.connections.mariadb.database', $newDatabase[$selectedDatabase]);
            app('config')->set('database.connections.mariadb.database', $newDatabase[$selectedDatabase]);

            // Artisan::call('config:clear');
            // Artisan::call('cache:clear');
            // Artisan::call('config:cache'); // Re-cache the configuration

            // Purge and reconnect to apply changes
            app('db')->purge('mariadb');
            app('db')->reconnect('mariadb');
            session()->reflash();

            // dd(session()->all());

            return redirect()->back()->with('success', 'Database switched to ' . ucfirst($database));
        }

        return redirect()->back()->with('error', 'Invalid database selection.');
    }


    public function profile()
    {
        $user = Auth::user();
        // $user = User::find(1);

        return view('profile', [
            'user' => $user,
        ]);
    }

    public function editAccount(Request $request)
    {
        $user = User::find($request->users_id);

        return view('edit-user', [
            "user" => $user,
        ]);
    }

    public function createAccount(Request $request)
    {
        $roles = Role::all();

        return view('create-user', compact('roles'));
    }

    public function storeAccount(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'role_new' => 'required|string',

        ]);

        // Hash the password before creating
        $validated['password'] = Hash::make($validated['password']);

        // Create the user using mass assignment
        $validated['role'] = $request->role;
        $user = User::create($validated);
        $user->assignRole($validated['role_new']);
        $user->save();

        return redirect()->intended('/account-maintenance')->with('success', 'Account created successfully!');
    }

    public function updateProfile(Request $request)
    {
        $user = User::find($request->users_id); // Get logged-in user

        // Validate input
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:6|confirmed', // 'confirmed' ensures password_confirmation field matches
        ]);

        // Update user data
        $user->name = $request->name;
        $user->email = $request->email;

        // Check if password is provided
        if ($request->password) {
            $user->password = Hash::make($request->password);
        }

        $user->save(); // Save changes

        return redirect()->intended('/account-maintenance')->with('success', 'Profile updated successfully!');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $remember_me = $request->has('remember_me');

        // Coba login dengan Auth::attempt()
        if (Auth::attempt($credentials, $remember_me)) {
            session()->put('auth_user_id', Auth::id()); // Simpan user ID manual
            session()->save(); // Paksa Laravel menyimpan session
            return redirect()->intended('/');
        }

        // Jika gagal, cek user secara manual dan login
        $user = User::where('email', $request->email)->first();
        if ($user && Hash::check($request->password, $user->password)) {
            Auth::login($user, $remember_me);
            session()->put('auth_user_id', Auth::id());
            session()->save();
            return redirect()->intended('/');
        }

        return back()->with('loginError', 'Email atau Password salah!');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->intended('/signin'); // Redirect to login after logout
    }

    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }
}
