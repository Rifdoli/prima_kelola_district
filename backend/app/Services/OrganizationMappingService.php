<?php

namespace App\Services;

use App\Models\Organization;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OrganizationMappingService
{
    /**
     * Insert closure rows for a freshly created organization.
     *
     * The new node inherits every ancestor of its parent (each one depth+1),
     * plus a self row at depth 0. If the organization has no parent (root),
     * only the self row is inserted.
     */
    public function insertNode(Organization $organization): void
    {
        DB::transaction(function () use ($organization) {
            $now = now();
            $rows = [];

            if ($organization->parent_organization_id) {
                $parentAncestors = DB::table('organization_mapping')
                    ->where('descendant_id', $organization->parent_organization_id)
                    ->get(['ancestor_id', 'depth']);

                foreach ($parentAncestors as $ancestor) {
                    $rows[] = [
                        'ancestor_id' => $ancestor->ancestor_id,
                        'descendant_id' => $organization->organization_id,
                        'depth' => $ancestor->depth + 1,
                        'is_active' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            $rows[] = [
                'ancestor_id' => $organization->organization_id,
                'descendant_id' => $organization->organization_id,
                'depth' => 0,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            DB::table('organization_mapping')->insert($rows);
        });
    }

    /**
     * Remove closure rows for an organization being deleted.
     *
     * The `organization_mapping` FKs are `cascadeOnDelete`, so the database
     * already removes every row touching this node once the organization
     * row itself is deleted. This method exists only so callers don't have
     * to know that detail — it is intentionally a no-op.
     *
     * Callers are responsible for rejecting deletes of organizations that
     * still have children (see OrganizationController::destroy) - this
     * service does not enforce that policy.
     */
    public function deleteNode(Organization $organization): void
    {
        // Intentionally empty - see docblock above.
    }

    /**
     * Re-parent an existing organization's subtree.
     *
     * Not supported in this MVP (see issue #22, decision 5c/#2). Moving a
     * node requires rewriting closure rows for the entire subtree being
     * moved, which needs its own tests before it can be trusted.
     */
    public function moveNode(Organization $organization, ?int $newParentId): void
    {
        throw new RuntimeException('Moving an organization to a new parent is not supported yet.');
    }
}
