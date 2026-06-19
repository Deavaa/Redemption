<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class BankIntegration extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'bank_name',
        'bank_code',
        'account_number',
        'account_name',
        'integration_type',
        'api_url',
        'api_key',
        'api_secret',
        'merchant_id',
        'currency',
        'is_active',
        'notes',
    ];

    // Sensitive fields — never expose in JSON/array responses or logs.
    protected $hidden = [
        'api_key',
        'api_secret',
        'account_number',
        'merchant_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function transactions()
    {
        return $this->hasMany(BankTransaction::class);
    }

    public function getDecryptedApiKey(): string
    {
        try {
            return $this->api_key ? Crypt::decryptString($this->api_key) : '';
        } catch (\Exception $e) {
            return $this->api_key ?? '';
        }
    }

    public function getDecryptedApiSecret(): string
    {
        try {
            return $this->api_secret ? Crypt::decryptString($this->api_secret) : '';
        } catch (\Exception $e) {
            return $this->api_secret ?? '';
        }
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public static function ethiopianBanks(): array
    {
        return [
            'CBE' => 'Commercial Bank of Ethiopia',
            'Awash' => 'Awash International Bank',
            'Dashen' => 'Dashen Bank',
            'Abyssinia' => 'Bank of Abyssinia',
            'United' => 'United Bank',
            'Nib' => 'Nib International Bank',
            'Berhan' => 'Berhan International Bank',
            'Abay' => 'Abay Bank',
            'Cooperative' => 'Cooperative Bank of Oromia',
            'Oromia' => 'Oromia International Bank',
            'Bunna' => 'Bunna International Bank',
            'Zemen' => 'Zemen Bank',
            'Enat' => 'Enat Bank',
            'Wegagen' => 'Wegagen Bank',
            'Amhara' => 'Amhara Bank',
            'Tsehay' => 'Tsehay Bank',
            'Hijra' => 'Hijra Bank',
            'Dukem' => 'Dukem Bank',
        ];
    }

    public static function integrationTypes(): array
    {
        return [
            'csv_upload' => 'CSV/Statement Upload',
            'api' => 'Bank API Integration',
            'manual' => 'Manual Entry',
        ];
    }
}
