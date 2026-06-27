<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->success(Role::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * `sname` is an explicit input (not derived from `name`) and is the
     * stable identifier authorization checks rely on — it can never be
     * changed afterwards (see update()).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'sname' => ['required', 'string', 'max:50', 'unique:roles,sname'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $role = Role::create($validated);

        return $this->success($role, 'Role created.', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        return $this->success($role);
    }

    /**
     * Update the specified resource in storage.
     *
     * `sname` is intentionally not accepted here — only `name`,
     * `description`, and `is_active` can be changed after a role is
     * created.
     */
    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255', 'unique:roles,name,'.$role->getKey().',role_id'],
            'description' => ['sometimes', 'nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $role->update($validated);

        return $this->success($role, 'Role updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        $role->delete();

        return $this->success(null, 'Role deleted.');
    }
}
