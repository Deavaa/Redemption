<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BranchTelegramSetting extends Model
{
    protected $fillable = ['branch_id', 'bot_token', 'chat_id', 'is_enabled', 'welcome_message'];

    protected $casts = ['is_enabled' => 'boolean'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get settings for a specific branch.
     */
    public static function getForBranch(int $branchId): ?self
    {
        return static::where('branch_id', $branchId)->first();
    }

    /**
     * Get or create settings for a branch.
     */
    public static function getOrCreateForBranch(int $branchId): self
    {
        return static::firstOrCreate(
            ['branch_id' => $branchId],
            ['is_enabled' => false]
        );
    }
}
