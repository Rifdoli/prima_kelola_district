<?php

namespace App\Services;

use App\Models\Organization;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

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
     * Re-parent an existing organization, moving its entire subtree with it.
     *
     * Internal links within the subtree (both endpoints inside it) are left
     * untouched - their relative depth doesn't change when the subtree
     * moves. Only external links (between the subtree and anything above
     * it) are rewritten: the old ones are dropped, then new ones are built
     * from every ancestor of the new parent (including the new parent
     * itself) to every node in the subtree (including the moved node
     * itself), via a cross join on the closure table.
     *
     * @param  int|null  $newParentId  Null moves the organization to root.
     *
     * @throws InvalidArgumentException if $newParentId is the organization
     *         itself, or lies inside its own subtree (which would create a
     *         cycle).
     */
    public function moveNode(Organization $organization, ?int $newParentId): void
    {
        $organizationId = $organization->organization_id;

        if ($newParentId === $organizationId) {
            throw new InvalidArgumentException('An organization cannot be moved under itself.');
        }

        if ($newParentId !== null) {
            $newParentIsInSubtree = DB::table('organization_mapping')
                ->where('ancestor_id', $organizationId)
                ->where('descendant_id', $newParentId)
                ->exists();

            if ($newParentIsInSubtree) {
                throw new InvalidArgumentException('An organization cannot be moved under one of its own descendants.');
            }
        }

        DB::transaction(function () use ($organization, $organizationId, $newParentId) {
            $now = now();

            $subtreeIds = DB::table('organization_mapping')
                ->where('ancestor_id', $organizationId)
                ->pluck('descendant_id');

            DB::table('organization_mapping')
                ->whereIn('descendant_id', $subtreeIds)
                ->whereNotIn('ancestor_id', $subtreeIds)
                ->delete();

            if ($newParentId !== null) {
                $newAncestors = DB::table('organization_mapping')
                    ->where('descendant_id', $newParentId)
                    ->get(['ancestor_id', 'depth']);

                $subtreeNodes = DB::table('organization_mapping')
                    ->where('ancestor_id', $organizationId)
                    ->get(['descendant_id', 'depth']);

                $rows = [];
                foreach ($newAncestors as $superAncestor) {
                    foreach ($subtreeNodes as $subNode) {
                        $rows[] = [
                            'ancestor_id' => $superAncestor->ancestor_id,
                            'descendant_id' => $subNode->descendant_id,
                            'depth' => $superAncestor->depth + $subNode->depth + 1,
                            'is_active' => true,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }

                DB::table('organization_mapping')->insert($rows);
            }

            $organization->parent_organization_id = $newParentId;
            $organization->save();
        });
    }
}
