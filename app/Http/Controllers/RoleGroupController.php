<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RoleGroup;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RoleGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roleGroups = RoleGroup::withRoles()
            ->ordered()
            ->get();

        return view('role-groups.index', compact('roleGroups'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('role-groups.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:role_groups',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        try {
            RoleGroup::create([
                'name' => $request->name,
                'display_name' => $request->display_name,
                'description' => $request->description,
                'color' => $request->color ?: '#6c757d',
                'icon' => $request->icon ?: 'fas fa-users',
                'sort_order' => $request->sort_order ?: 0,
                'is_active' => true,
            ]);

            return redirect()->route('role-groups.index')
                ->with('success', 'Role group berhasil dibuat.');

        } catch (\Exception $e) {
            Log::error('Error creating role group: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat membuat role group.')
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(RoleGroup $roleGroup)
    {
        $roleGroup->load(['roles' => function ($query) {
            $query->withCount('users')->orderBy('name');
        }]);

        return view('role-groups.show', compact('roleGroup'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RoleGroup $roleGroup)
    {
        return view('role-groups.edit', compact('roleGroup'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RoleGroup $roleGroup)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:role_groups,name,' . $roleGroup->id,
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        try {
            $roleGroup->update([
                'name' => $request->name,
                'display_name' => $request->display_name,
                'description' => $request->description,
                'color' => $request->color ?: '#6c757d',
                'icon' => $request->icon ?: 'fas fa-users',
                'sort_order' => $request->sort_order ?: 0,
                'is_active' => $request->has('is_active'),
            ]);

            return redirect()->route('role-groups.index')
                ->with('success', 'Role group berhasil diupdate.');

        } catch (\Exception $e) {
            Log::error('Error updating role group: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat mengupdate role group.')
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RoleGroup $roleGroup)
    {
        try {
            // Check if role group has roles
            if ($roleGroup->roles()->count() > 0) {
                return back()->with('error', 'Tidak dapat menghapus role group yang masih memiliki roles. Pindahkan atau hapus roles terlebih dahulu.');
            }

            $roleGroup->delete();

            return redirect()->route('role-groups.index')
                ->with('success', 'Role group berhasil dihapus.');

        } catch (\Exception $e) {
            Log::error('Error deleting role group: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menghapus role group.');
        }
    }

    /**
     * Assign role to group
     */
    public function assignRole(Request $request, RoleGroup $roleGroup)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        try {
            $role = Role::findOrFail($request->role_id);
            $role->update(['group_id' => $roleGroup->id]);

            return back()->with('success', "Role '{$role->name}' berhasil ditambahkan ke group '{$roleGroup->display_name}'.");

        } catch (\Exception $e) {
            Log::error('Error assigning role to group: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menambahkan role ke group.');
        }
    }

    /**
     * Remove role from group
     */
    public function removeRole(RoleGroup $roleGroup, Role $role)
    {
        try {
            $role->update(['group_id' => null]);

            return back()->with('success', "Role '{$role->name}' berhasil dihapus dari group '{$roleGroup->display_name}'.");

        } catch (\Exception $e) {
            Log::error('Error removing role from group: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menghapus role dari group.');
        }
    }

    /**
     * Get available roles for assignment
     */
    public function getAvailableRoles(RoleGroup $roleGroup)
    {
        $availableRoles = Role::whereNull('group_id')
            ->orWhere('group_id', '!=', $roleGroup->id)
            ->orderBy('name')
            ->get();

        return response()->json($availableRoles);
    }

    /**
     * Toggle role group status
     */
    public function toggleStatus(RoleGroup $roleGroup)
    {
        try {
            $roleGroup->update(['is_active' => !$roleGroup->is_active]);

            $status = $roleGroup->is_active ? 'diaktifkan' : 'dinonaktifkan';
            return back()->with('success', "Role group '{$roleGroup->display_name}' berhasil {$status}.");

        } catch (\Exception $e) {
            Log::error('Error toggling role group status: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat mengubah status role group.');
        }
    }
}