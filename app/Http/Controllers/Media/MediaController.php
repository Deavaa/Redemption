<?php

namespace App\Http\Controllers\Media;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    /**
     * Serve a file from storage/app/public as a fallback when the symlink doesn't work.
     * This route handles /storage/{path} requests directly through PHP.
     *
     * @param  string  $path
     * @return \Illuminate\Http\Response
     */
    public function serve($path)
    {
        // Security: prevent directory traversal attacks
        $path = str_replace(['../', '..\\'], '', $path);

        // Check if the file exists in storage/app/public
        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'File not found.');
        }

        // Get the full path to the file
        $fullPath = Storage::disk('public')->path($path);

        // Get the file's mime type
        $mimeType = mime_content_type($fullPath);

        if ($mimeType === false) {
            $mimeType = 'application/octet-stream';
        }

        // Get the file size
        $fileSize = Storage::disk('public')->size($path);

        // Stream the file as a response with proper headers
        return Response::stream(function () use ($fullPath) {
            $stream = fopen($fullPath, 'rb');
            if ($stream === false) {
                abort(500, 'Unable to read file.');
            }
            while (!feof($stream)) {
                echo fread($stream, 8192);
                flush();
            }
            fclose($stream);
        }, 200, [
            'Content-Type' => $mimeType,
            'Content-Length' => $fileSize,
            'Cache-Control' => 'public, max-age=31536000',
            'Expires' => gmdate('D, d M Y H:i:s \G\M\T', time() + 31536000),
        ]);
    }

    /**
     * Handle image uploads. Stores the file in storage/app/public/ and
     * also copies it to public/storage/ as a fallback for when the symlink
     * is not available (e.g., on XAMPP setups).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|image|max:10240', // max 10MB
            'directory' => 'nullable|string|max:255',
        ]);

        $directory = $request->input('directory', 'uploads');
        // Sanitize directory name
        $directory = preg_replace('/[^a-zA-Z0-9_\-\/]/', '', $directory);

        $file = $request->file('file');

        // Generate a unique filename
        $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();

        // Store the file in storage/app/public/{directory}
        $path = $file->storeAs($directory, $filename, 'public');

        // Also copy the file to public/storage/ as a fallback for XAMPP/symlink issues
        $this->copyToPublicStorage($path);

        // Return the path that can be used to reference the file
        // Using /storage/ path which will work via either the symlink or the fallback route
        return response()->json([
            'success' => true,
            'path' => $path,
            'url' => '/storage/' . $path,
            'filename' => $filename,
        ]);
    }

    /**
     * Copy a file from storage/app/public/ to public/storage/ as a fallback.
     * This ensures images are accessible even when the symlink doesn't exist.
     *
     * @param  string  $relativePath  The path relative to storage/app/public/
     * @return void
     */
    private function copyToPublicStorage($relativePath)
    {
        try {
            $sourcePath = Storage::disk('public')->path($relativePath);
            $destinationPath = public_path('storage/' . $relativePath);

            // Ensure the destination directory exists
            $destinationDir = dirname($destinationPath);
            if (!is_dir($destinationDir)) {
                mkdir($destinationDir, 0755, true);
            }

            // Copy the file if the source exists
            if (file_exists($sourcePath)) {
                copy($sourcePath, $destinationPath);
            }
        } catch (\Exception $e) {
            // Log the error but don't fail the upload
            \Log::warning('Failed to copy file to public storage fallback: ' . $e->getMessage());
        }
    }
}
