<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Setting extends Model
{
    use HasFactory;
    protected $fillable = ['key','value','group','type','description'];
    public static function get($key, $default = null) {
        $s = static::where('key', $key)->first();
        return $s ? $s->value : $default;
    }
    public static function set($key, $value) {
        return static::updateOrCreate(['key'=>$key], ['value'=>$value]);
    }

    /**
     * Get the school name in the current locale.
     * If the current locale is Amharic (am) and school_name_am is set, use that.
     * Otherwise fall back to school_name.
     */
    public static function getLocalizedName(): string
    {
        $locale = app()->getLocale();
        if ($locale === 'am') {
            $amName = self::get('school_name_am');
            if (!empty($amName)) {
                return $amName;
            }
            // Default Amharic name if school_name_am is not set in the database
            return 'ስኩል ኦፍ ሪደምሽን';
        }
        return self::get('school_name', 'School of Redemption');
    }

    /**
     * Get the URL for a file-type setting value.
     * Works for any setting key (logo, favicon, etc.) that stores a file path.
     * Uses the same robust fallback strategy as getLogoUrl().
     */
    public static function getFileUrl(string $key): string
    {
        $value = self::get($key);

        if (empty($value)) {
            return '';
        }

        // Strategy 1: Direct public path (public/storage/settings/logo.png)
        if (file_exists(public_path('storage/' . $value))) {
            return asset('storage/' . $value);
        }

        // Strategy 2: Storage facade (checks storage/app/public/)
        try {
            if (Storage::disk('public')->exists($value)) {
                self::ensurePublicCopy($value);
                return asset('storage/' . $value);
            }
        } catch (\Throwable $e) {
            \Log::warning('Setting::getFileUrl Strategy 2 failed: ' . $e->getMessage());
        }

        // Strategy 3: Storage app public directory (real file location)
        if (file_exists(storage_path('app/public/' . $value))) {
            self::ensurePublicCopy($value);
            return asset('storage/' . $value);
        }

        // Strategy 4: Direct public path without storage prefix
        if (file_exists(public_path($value))) {
            return asset($value);
        }

        // Strategy 5: Check uploads directory
        if (file_exists(public_path('uploads/' . $value))) {
            return asset('uploads/' . $value);
        }

        // Strategy 6: Last resort
        return asset('storage/' . $value);
    }

    /**
     * Get the full URL for the school logo
     * Uses multiple fallback strategies for different server setups (XAMPP, shared hosting, etc.)
     * Also ensures the file is copied to public/storage/ for symlink-less setups.
     */
    public static function getLogoUrl(): string
    {
        $logo = self::get('school_logo') ?? self::get('logo') ?? '';

        if (empty($logo)) {
            return '';
        }

        // Strategy 1: Direct public path (public/storage/settings/logo.png) — fastest, works with copyToPublicStorage
        // But skip if public/storage is a valid symlink (file would be accessible via symlink anyway)
        if (file_exists(public_path('storage/' . $logo)) && !is_link(public_path('storage'))) {
            return asset('storage/' . $logo);
        }

        // Strategy 2: Storage facade (standard Laravel — checks storage/app/public/)
        try {
            if (Storage::disk('public')->exists($logo)) {
                // Try to copy to public/storage/ for next time
                self::ensurePublicCopy($logo);
                // Use asset() instead of Storage::url() — asset() respects the
                // current request host/port, while Storage::url() hardcodes APP_URL
                // which may not match the actual URL the browser is using.
                return asset('storage/' . $logo);
            }
        } catch (\Throwable $e) {
            \Log::warning('Setting::getLogoUrl Strategy 2 failed: ' . $e->getMessage());
        }

        // Strategy 3: Storage app public directory (real file location)
        if (file_exists(storage_path('app/public/' . $logo))) {
            self::ensurePublicCopy($logo);
            return asset('storage/' . $logo);
        }

        // Strategy 4: Direct public path without storage prefix
        if (file_exists(public_path($logo))) {
            return asset($logo);
        }

        // Strategy 5: Check uploads directory
        if (file_exists(public_path('uploads/' . $logo))) {
            return asset('uploads/' . $logo);
        }

        // Strategy 6: Last resort — assume web server can serve via symlink or fallback route
        return asset('storage/' . $logo);
    }

    /**
     * Ensure a file from storage/app/public/ is also copied to public/storage/
     * for servers where the symlink doesn't work (XAMPP).
     */
    private static function ensurePublicCopy(string $relativePath): void
    {
        try {
            $sourcePath = storage_path('app/public/' . $relativePath);
            $destinationPath = public_path('storage/' . $relativePath);

            // Check if public/storage is a symlink — if so, no need to copy
            $publicStorage = public_path('storage');
            if (is_link($publicStorage)) {
                return;
            }

            if (file_exists($sourcePath) && !file_exists($destinationPath)) {
                $destinationDir = dirname($destinationPath);
                if (!is_dir($destinationDir)) {
                    // Remove public/storage if it's a file (not directory) — could be a broken link
                    if (file_exists($publicStorage) && !is_dir($publicStorage)) {
                        @unlink($publicStorage);
                    }
                    mkdir($destinationDir, 0755, true);
                }
                copy($sourcePath, $destinationPath);
                chmod($destinationPath, 0644);
            }
        } catch (\Throwable $e) {
            \Log::warning('Setting::ensurePublicCopy failed for "' . $relativePath . '": ' . $e->getMessage());
        }
    }
}
