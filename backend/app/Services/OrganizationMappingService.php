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
     * Count how many descendants an organization has, excluding itself.
     *
     * Used by callers to warn before a cascading delete.
     */
    public function descendantCount(Organization $organization): int
    {
        return DB::table('organization_mapping')
            ->where('ancestor_id', $organization->organization_id)
            ->where('descendant_id', '!=', $organization->organization_id)
            ->count();
    }

    /**
     * Delete an organization together with its entire subtree.
     *
     * The whole subtree (the node itself plus every descendant) is resolved
     * from the closure table, then deleted in one statement. Removing the
     * organization rows automatically clears their closure rows - the
     * `ancestor_id`/`descendant_id` FKs are `cascadeOnDelete` - including the
     * links that tied the subtree to ancestors above it. Any user whose
     * `organization_id` pointed at a deleted org is set to null (that FK is
     * `nullOnDelete`).
     *
     * Returns the number of organizations deleted.
     */
    public function deleteSubtree(Organization $organization): int
    {
        return DB::transaction(function () use ($organization) {
            $subtreeIds = DB::table('organization_mapping')
                ->where('ancestor_id', $organization->organization_id)
                ->pluck('descendant_id')
                ->all();

            return Organization::whereIn('organization_id', $subtreeIds)->delete();
        });
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
