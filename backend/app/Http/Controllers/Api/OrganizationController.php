<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Services\OrganizationMappingService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class OrganizationController extends Controller
{
    use ApiResponse;

    public function __construct(protected OrganizationMappingService $mapping) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->success(Organization::with('type', 'parent')->orderBy('organization_id')->get());
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
     * Move the organization (and its entire subtree) under a new parent.
     *
     * This is a separate endpoint from update() on purpose: re-parenting
     * has its own validation (the anti-cycle check) and rewrites the
     * closure table for the whole subtree, which is a meaningfully
     * different operation from editing the organization's own fields.
     */
    public function move(Request $request, Organization $organization)
    {
        $validated = $request->validate([
            'parent_organization_id' => ['nullable', 'exists:organizations,organization_id'],
        ]);

        try {
            $this->mapping->moveNode($organization, $validated['parent_organization_id'] ?? null);
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success($organization->refresh()->load('type', 'parent'), 'Organization moved.');
    }

    /**
     * Update the specified resource in storage.
     *
     * `parent_organization_id` is intentionally not accepted here - use the
     * dedicated move() endpoint to re-parent an organization.
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
     *
     * Deleting an organization that has descendants removes the entire
     * subtree. To guard against an accidental mass delete (e.g. deleting a
     * top-level org would wipe everything under it), the caller must opt in
     * with `cascade=true` whenever descendants exist.
     */
    public function destroy(Request $request, Organization $organization)
    {
        $descendantCount = $this->mapping->descendantCount($organization);

        if ($descendantCount > 0 && ! $request->boolean('cascade')) {
            return $this->error(
                "This organization has {$descendantCount} descendant organization(s). Pass cascade=true to delete it together with its entire subtree.",
                422
            );
        }

        $deleted = $this->mapping->deleteSubtree($organization);

        return $this->success(null, "Organization deleted ({$deleted} total).");
    }
}
