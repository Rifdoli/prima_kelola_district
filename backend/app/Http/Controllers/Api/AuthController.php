<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use ApiResponse;

    public function register(Request $request)
    {
        $isLdap = $request->boolean('is_ldap');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'is_ldap' => ['sometimes', 'boolean'],
            'password' => $isLdap
                ? ['nullable', 'string']
                : ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // LDAP accounts authenticate against the directory, not a local
        // password — store an unusable random one to satisfy the NOT NULL column.
        $password = $isLdap ? Str::random(32) : $validated['password'];

        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'] ?? null,
            'is_ldap' => $isLdap,
            'password' => Hash::make($password),
        ])->refresh();

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->success([
            'user' => $user->load('role', 'organization'),
            'token' => $token,
        ], 'Registration successful.', 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'username' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = User::where('username', $credentials['username'])->first();
        $user->forceFill(['last_login_at' => now()])->save();
        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->success([
            'user' => $user->load('role', 'organization'),
            'token' => $token,
        ], 'Login successful.');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'Logout successful.');
    }

    public function me(Request $request)
    {
        return $this->success($request->user()->load('role', 'organization'), 'Authenticated user retrieved.');
    }
}
