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
        if (file_exists(public_path('storage/' . $logo))) {
            return asset('storage/' . $logo);
        }

        // Strategy 2: Storage facade (standard Laravel — checks storage/app/public/)
        try {
            if (Storage::disk('public')->exists($logo)) {
                // Try to copy to public/storage/ for next time
                self::ensurePublicCopy($logo);
                return Storage::url($logo);
            }
        } catch (\Throwable $e) {}

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

            if (file_exists($sourcePath) && !file_exists($destinationPath)) {
                $destinationDir = dirname($destinationPath);
                if (!is_dir($destinationDir)) {
                    mkdir($destinationDir, 0755, true);
                }
                copy($sourcePath, $destinationPath);
            }
        } catch (\Throwable $e) {
            // Silently fail — don't break the page
        }
    }
}
