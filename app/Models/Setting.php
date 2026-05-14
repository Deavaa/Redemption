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
     */
    public static function getLogoUrl(): string
    {
        $logo = self::get('school_logo') ?? self::get('logo') ?? '';

        if (empty($logo)) {
            return asset('images/default-logo.png');
        }

        // Check if file exists in public path
        if (file_exists(public_path($logo))) {
            return asset($logo);
        }

        // Check if file exists in storage
        if (file_exists(storage_path('app/public/' . $logo))) {
            return Storage::url($logo);
        }

        // Check with uploads prefix
        if (file_exists(public_path('uploads/' . $logo))) {
            return asset('uploads/' . $logo);
        }

        // Try as-is with asset
        return asset($logo);
    }
}
