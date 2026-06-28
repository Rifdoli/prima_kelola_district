<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Services\OrganizationMappingService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrganizationController extends Controller
{
    use ApiResponse;

    public function __construct(protected OrganizationMappingService $mapping) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->success(Organization::with('type', 'parent')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sname' => ['required', 'string', 'max:50', 'unique:organizations,sname'],
            'organization_type_id' => ['required', 'exists:organization_types,organization_type_id'],
            'parent_organization_id' => ['nullable', 'exists:organizations,organization_id'],
            'timezone' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $organization = DB::transaction(function () use ($validated) {
            $organization = Organization::create($validated)->refresh();
            $this->mapping->insertNode($organization);

            return $organization;
        });

        return $this->success($organization->load('type', 'parent'), 'Organization created.', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Organization $organization)
    {
        return $this->success($organization->load('type', 'parent'));
    }

    /**
     * Update the specified resource in storage.
     *
     * `parent_organization_id` is intentionally not accepted here - moving
     * an organization to a new parent is not supported yet (see issue #22).
     */
    public function update(Request $request, Organization $organization)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'sname' => ['sometimes', 'string', 'max:50', 'unique:organizations,sname,'.$organization->getKey().',organization_id'],
            'organization_type_id' => ['sometimes', 'exists:organization_types,organization_type_id'],
            'timezone' => ['sometimes', 'nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
            'notes' => ['sometimes', 'nullable', 'string'],
        ]);

        $organization->update($validated);

        return $this->success($organization->load('type', 'parent'), 'Organization updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Organization $organization)
    {
        if ($organization->children()->exists()) {
            return $this->error('Cannot delete an organization that still has child organizations.', 422);
        }

        $organization->delete();

        return $this->success(null, 'Organization deleted.');
    }
}
