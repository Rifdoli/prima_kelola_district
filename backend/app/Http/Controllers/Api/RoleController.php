<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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
     * `slug` is derived once from `name` and never changes afterwards
     * (see update()) — it's the stable identifier authorization checks
     * rely on, kept separate from the freely-editable `name` label.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
        ]);

        $slug = Str::slug($validated['name']);

        if (Role::where('slug', $slug)->exists()) {
            throw ValidationException::withMessages([
                'name' => ['A role with an equivalent slug already exists.'],
            ]);
        }

        $role = Role::create([
            'name' => $validated['name'],
            'slug' => $slug,
        ]);

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
     * `slug` is intentionally not accepted here — only `name` (the
     * display label) can be changed after a role is created.
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
