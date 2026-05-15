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
     */
    public static function getLogoUrl(): string
    {
        $logo = self::get('school_logo') ?? self::get('logo') ?? '';

        if (empty($logo)) {
            return '';
        }

        // Strategy 1: Storage facade (standard Laravel)
        try {
            if (Storage::disk('public')->exists($logo)) {
                return Storage::url($logo);
            }
        } catch (\Throwable $e) {}

        // Strategy 2: Direct public path (e.g. public/storage/settings/logo.png via symlink)
        if (file_exists(public_path('storage/' . $logo))) {
            return asset('storage/' . $logo);
        }

        // Strategy 3: Direct public path without storage prefix
        if (file_exists(public_path($logo))) {
            return asset($logo);
        }

        // Strategy 4: Check uploads directory
        if (file_exists(public_path('uploads/' . $logo))) {
            return asset('uploads/' . $logo);
        }

        // Strategy 5: Storage app public directory (real file location)
        if (file_exists(storage_path('app/public/' . $logo))) {
            return asset('storage/' . $logo);
        }

        // Strategy 6: Try the raw value as a URL path (works if symlink exists on server)
        // This is a last-resort fallback that assumes the web server can serve the file
        return asset('storage/' . $logo);
    }
}
