<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->success(User::with('role')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8'],
            'role_id' => ['nullable', 'exists:roles,role_id'],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated)->refresh();

        return $this->success($user, 'User created.', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return $this->success($user->load('role'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'username' => ['sometimes', 'string', 'max:255', 'unique:users,username,'.$user->getKey().',user_id'],
            'email' => ['sometimes', 'string', 'email', 'max:255', 'unique:users,email,'.$user->getKey().',user_id'],
            'phone_number' => ['sometimes', 'nullable', 'string', 'max:20'],
            'is_active' => ['sometimes', 'boolean'],
            'role_id' => ['sometimes', 'nullable', 'exists:roles,role_id'],
        ]);

        $user->update($validated);

        return $this->success($user->load('role'), 'User updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return $this->success(null, 'User deleted.');
    }
}
