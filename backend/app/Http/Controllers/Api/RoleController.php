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
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
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
     */
    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,'.$role->id],
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
