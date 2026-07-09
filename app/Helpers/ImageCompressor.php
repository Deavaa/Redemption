<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

/**
 * ImageCompressor — automatic image compression using PHP GD library.
 *
 * Compresses uploaded images to stay under a max file size by:
 * 1. Resizing if the image is larger than max dimensions (default 1920px)
 * 2. Adjusting JPEG quality progressively until under the size limit
 *
 * No external packages required — uses PHP's built-in GD extension
 * (available on all cPanel and XAMPP installations).
 *
 * Usage:
 *   $path = ImageCompressor::compressAndStore($uploadedFile, 'news-images');
 *   // Returns relative path like "news-images/abc.jpg"
 */
class ImageCompressor
{
    /**
     * Compress an uploaded image and store it to the public disk + public/ fallback.
     *
     * @param UploadedFile $file  The uploaded file
     * @param string $directory   Target directory (e.g. 'news-images', 'gallery', 'team-photos')
     * @param int $maxSizeKB      Maximum file size in KB (default 2048 = 2MB)
     * @param int $maxDimension   Maximum width/height in pixels (default 1920)
     * @return string|null        Relative path on success, null on failure
     */
    public static function compressAndStore(UploadedFile $file, string $directory, int $maxSizeKB = 2048, int $maxDimension = 1920): ?string
    {
        if (!$file || !$file->isValid()) {
            return null;
        }

        // Check GD extension is available
        if (!extension_loaded('gd')) {
            Log::warning('ImageCompressor: GD extension not loaded — storing original file without compression');
            return self::storeOriginal($file, $directory);
        }

        $mimeType = $file->getMimeType();
        $originalSizeKB = round($file->getSize() / 1024);

        // Only process image types GD can handle
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($mimeType, $allowedTypes)) {
            Log::info('ImageCompressor: unsupported type, storing original', ['mime' => $mimeType]);
            return self::storeOriginal($file, $directory);
        }

        // Generate filename
        $extension = self::getExtension($mimeType, $file->getClientOriginalExtension());
        $filename = self::generateFilename($directory, $extension);
        $relativePath = $directory . '/' . $filename;

        // Create the source image resource
        $sourcePath = $file->getRealPath();
        $image = self::createImageResource($sourcePath, $mimeType);
        if (!$image) {
            Log::error('ImageCompressor: failed to create image resource', ['path' => $sourcePath]);
            return self::storeOriginal($file, $directory);
        }

        // Get original dimensions
        $origWidth = imagesx($image);
        $origHeight = imagesy($image);

        // Resize if exceeds max dimension (maintain aspect ratio)
        $newWidth = $origWidth;
        $newHeight = $origHeight;
        if ($origWidth > $maxDimension || $origHeight > $maxDimension) {
            $ratio = min($maxDimension / $origWidth, $maxDimension / $origHeight);
            $newWidth = (int)($origWidth * $ratio);
            $newHeight = (int)($origHeight * $ratio);

            $resized = imagecreatetruecolor($newWidth, $newHeight);
            // Preserve transparency for PNG/GIF
            if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
                $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
                imagefilledrectangle($resized, 0, 0, $newWidth, $newHeight, $transparent);
            }
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
            imagedestroy($image);
            $image = $resized;
        }

        // Determine output format — convert GIF to JPEG (better compression for photos)
        // Keep PNG as PNG (preserves transparency), convert everything else to JPEG
        $outputType = ($mimeType === 'image/png') ? 'png' : 'jpeg';
        if ($mimeType === 'image/webp' && function_exists('imagewebp')) {
            $outputType = 'webp';
        }

        // Progressive quality compression for JPEG/WEBP
        $quality = 85;       // Start at 85% quality
        $minQuality = 40;    // Don't go below 40%
        $compressedData = null;

        while ($quality >= $minQuality) {
            ob_start();
            if ($outputType === 'jpeg') {
                imagejpeg($image, null, $quality);
            } elseif ($outputType === 'png') {
                // PNG quality is 0-9 (0 = no compression, 9 = max compression)
                // Convert quality percentage to PNG level (higher quality = lower level)
                $pngLevel = (int)((100 - $quality) / 10);
                imagepng($image, null, max(0, min(9, $pngLevel)));
            } elseif ($outputType === 'webp') {
                imagewebp($image, null, $quality);
            }
            $compressedData = ob_get_clean();

            $dataSizeKB = strlen($compressedData) / 1024;
            if ($dataSizeKB <= $maxSizeKB) {
                break; // Under the size limit — done!
            }

            $quality -= 10; // Reduce quality and try again
        }

        imagedestroy($image);

        if (!$compressedData) {
            Log::error('ImageCompressor: compression produced no data');
            return self::storeOriginal($file, $directory);
        }

        $finalSizeKB = round(strlen($compressedData) / 1024);
        Log::info('ImageCompressor: compressed', [
            'original' => $originalSizeKB . 'KB',
            'compressed' => $finalSizeKB . 'KB',
            'quality' => $quality,
            'dimensions' => $newWidth . 'x' . $newHeight,
            'directory' => $directory,
        ]);

        // Save to BOTH locations (same pattern as NewsController):
        // 1. storage/app/public/<directory>/<filename>  (Laravel standard)
        // 2. public/<directory>/<filename>              (directly web-accessible, no symlink needed)

        // Location 1: storage/app/public/
        $storageDir = storage_path('app/public/' . $directory);
        if (!is_dir($storageDir)) {
            @mkdir($storageDir, 0777, true);
        }
        $storagePath = $storageDir . '/' . $filename;
        @file_put_contents($storagePath, $compressedData);

        // Location 2: public/
        $publicDir = public_path($directory);
        if (!is_dir($publicDir)) {
            @mkdir($publicDir, 0777, true);
        }
        $publicPath = $publicDir . '/' . $filename;
        @file_put_contents($publicPath, $compressedData);

        if (!file_exists($publicPath) && !file_exists($storagePath)) {
            Log::error('ImageCompressor: failed to write file to either location');
            return null;
        }

        return $relativePath;
    }

    /**
     * Store the original file without compression (fallback).
     */
    private static function storeOriginal(UploadedFile $file, string $directory): ?string
    {
        try {
            $path = $file->store($directory, 'public');
            // Also copy to public/ as fallback
            $sourceFile = storage_path('app/public/' . $path);
            $publicDir = public_path($directory);
            if (!is_dir($publicDir)) {
                @mkdir($publicDir, 0777, true);
            }
            $publicPath = public_path($path);
            if (file_exists($sourceFile)) {
                @copy($sourceFile, $publicPath);
            }
            return $path;
        } catch (\Throwable $e) {
            Log::error('ImageCompressor: storeOriginal failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create a GD image resource from a file path.
     */
    private static function createImageResource(string $path, string $mimeType)
    {
        switch ($mimeType) {
            case 'image/jpeg':
            case 'image/jpg':
                return @imagecreatefromjpeg($path);
            case 'image/png':
                return @imagecreatefrompng($path);
            case 'image/gif':
                return @imagecreatefromgif($path);
            case 'image/webp':
                return function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false;
            default:
                return false;
        }
    }

    /**
     * Get the appropriate file extension based on mime type.
     */
    private static function getExtension(string $mimeType, string $originalExt): string
    {
        switch ($mimeType) {
            case 'image/png':
                return 'png';
            case 'image/gif':
                return 'gif';
            case 'image/webp':
                return 'webp';
            case 'image/jpeg':
            case 'image/jpg':
            default:
                return 'jpg';
        }
    }

    /**
     * Generate a unique filename.
     */
    private static function generateFilename(string $directory, string $extension): string
    {
        return 'img_' . date('Ymd_His') . '_' . \Illuminate\Support\Str::random(8) . '.' . $extension;
    }
}
