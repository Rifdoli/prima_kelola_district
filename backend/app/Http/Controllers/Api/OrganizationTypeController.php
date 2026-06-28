<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrganizationType;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class OrganizationTypeController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->success(OrganizationType::orderBy('organization_type_id')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:organization_types,name'],
            'level' => ['required', 'integer', 'min:1'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $organizationType = OrganizationType::create($validated);

        return $this->success($organizationType, 'Organization type created.', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(OrganizationType $organizationType)
    {
        return $this->success($organizationType);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, OrganizationType $organizationType)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255', 'unique:organization_types,name,'.$organizationType->getKey().',organization_type_id'],
            'level' => ['sometimes', 'integer', 'min:1'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $organizationType->update($validated);

        return $this->success($organizationType, 'Organization type updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OrganizationType $organizationType)
    {
        if ($organizationType->organizations()->exists()) {
            return $this->error('Cannot delete a type that is still used by an organization.', 422);
        }

        $organizationType->delete();

        return $this->success(null, 'Organization type deleted.');
    }
}
