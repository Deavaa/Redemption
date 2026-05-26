<?php

namespace App\Services;

use App\Models\ParentModel;
use Illuminate\Support\Facades\DB;

/**
 * Parent ID Generation Service
 *
 * Generates unique parent IDs.
 * Format: PAR-{NNNN}
 * Example: PAR-0001, PAR-0002
 */
class ParentIdService
{
    /**
     * Generate a unique parent ID.
     *
     * @return string The generated parent ID
     */
    public function generate(): string
    {
        // Get the next sequence number
        $lastParent = ParentModel::where('parent_id_number', 'LIKE', 'PAR-%')
            ->orderByRaw('CAST(SUBSTRING(parent_id_number, -4) AS UNSIGNED) DESC')
            ->first();

        $nextNumber = 1;
        if ($lastParent && $lastParent->parent_id_number) {
            $parts = explode('-', $lastParent->parent_id_number);
            $lastNumber = (int) end($parts);
            $nextNumber = $lastNumber + 1;
        }

        return 'PAR-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate and assign parent ID to a parent.
     *
     * @param ParentModel $parent
     * @return string The generated parent ID
     */
    public function assignToParent(ParentModel $parent): string
    {
        if (!empty($parent->parent_id_number)) {
            return $parent->parent_id_number;
        }

        $parentId = $this->generate();

        // Use direct DB update to avoid model events issues
        DB::table('parents')->where('id', $parent->id)->update(['parent_id_number' => $parentId]);

        return $parentId;
    }
}
