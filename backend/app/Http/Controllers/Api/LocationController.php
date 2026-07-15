<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocationController extends Controller
{
    use ApiResponse;

    /**
     * @todo implementasi filter by organisasi aktif
     * @todo implementasi pagination
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'status' => ['nullable', 'in:active,inactive,all'],
        ]);

        $params = [
            'status' => $request->input('status', 'active'),
        ];

        $query = Location::with('organization')
            ->orderBy('organization_id')
            ->orderBy('name');

        match ($params['status']) {
            'active' => $query->active(true),
            'inactive' => $query->active(false),
            default => null,
        };

        return $this->success(
            data: $query->get(),
            message: 'Locations retrieved successfully.'
        );
    }

    public function show(int $id): JsonResponse
    {
        $location = Location::with('organization')->findOrFail($id);
        return $this->success(
            data: $location,
            message: 'Location retrieved successfully.'
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sname' => ['required', 'string', 'max:10', 'unique:locations,sname'],
            'latitude' => ['nullable', 'string'],
            'longitude' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'organization_id' => ['nullable', 'exists:organizations,organization_id'],
        ]);

        $location = Location::create($validated);
        $location->load('organization');
        return $this->success(
            data: $location,
            message: 'Location created successfully.',
            status: 201
        );
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $location = Location::findOrFail($id);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sname' => [
                'required',
                'string',
                'max:10',
                Rule::unique('locations', 'sname')->ignore($location),
            ],
            'latitude' => ['nullable', 'string'],
            'longitude' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'organization_id' => ['nullable', 'exists:organizations,organization_id'],
        ]);

        $location->update($validated);
        $location->load('organization');
        return $this->success(
            data: $location,
            message: 'Location updated successfully.'
        );
    }

    public function activate(int $id): JsonResponse
    {
        $location = Location::with('organization')->findOrFail($id);
        if (!$location->is_active) {
            $location->is_active = true;
            $location->save();
        }

        return $this->success(
            data: $location,
            message: 'Location activated successfully.'
        );
    }

    public function deactivate(int $id): JsonResponse
    {
        $location = Location::with('organization')->findOrFail($id);
        if ($location->is_active) {
            $location->is_active = false;
            $location->save();
        }

        return $this->success(
            data: $location,
            message: 'Location deactivated successfully.'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        Location::destroy($id);
        return $this->success(
            data: null,
            message: 'Location removed successfully.'
        );
    }
}