<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FixStorageSymlink extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'storage:fix-link';

    /**
     * The console command description.
     */
    protected $description = 'Fix the public/storage symlink or copy files as fallback for shared hosting (cPanel)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $publicStorage = public_path('storage');
        $storageAppPublic = storage_path('app/public');

        // Check if public/storage is already a valid symlink
        if (is_link($publicStorage)) {
            $target = readlink($publicStorage);
            if ($target === $storageAppPublic || realpath($target) === realpath($storageAppPublic)) {
                $this->info('✓ public/storage symlink is already correct.');
                return 0;
            }
            $this->warn('public/storage is a symlink but points to: ' . $target);
            $this->info('Removing old symlink...');
            unlink($publicStorage);
        }

        // Check if public/storage is a directory with files
        if (is_dir($publicStorage)) {
            $this->info('public/storage exists as a directory (not a symlink).');
            $this->info('Copying files from storage/app/public to public/storage...');

            $copied = $this->copyDirectory($storageAppPublic, $publicStorage);
            $this->info("✓ Copied {$copied} files.");
            return 0;
        }

        // Try to create symlink
        $this->info('Attempting to create symlink...');
        try {
            if (symlink($storageAppPublic, $publicStorage)) {
                $this->info('✓ Symlink created successfully: public/storage -> storage/app/public');
                return 0;
            }
        } catch (\Exception $e) {
            $this->warn('Symlink creation failed: ' . $e->getMessage());
        }

        // Fallback: copy files
        $this->info('Falling back to file copy...');
        if (!is_dir($publicStorage)) {
            try {
                // Ensure the parent directory (public/) exists and is writable
                $parentDir = dirname($publicStorage);
                if (!is_dir($parentDir)) {
                    $this->warn('Parent directory does not exist: ' . $parentDir);
                    $this->info('Attempting to create parent directory...');
                    mkdir($parentDir, 0755, true);
                }
                if (!is_writable($parentDir)) {
                    $this->error('Parent directory is not writable: ' . $parentDir);
                    $this->warn('Try: chmod 755 ' . $parentDir . ' or run as administrator.');
                    return 1;
                }
                mkdir($publicStorage, 0755, true);
                $this->info('Created directory: ' . $publicStorage);
            } catch (\Exception $e) {
                $this->error('Cannot create directory: ' . $publicStorage);
                $this->error('Error: ' . $e->getMessage());
                $this->warn('Try creating it manually: mkdir -p ' . $publicStorage);
                $this->warn('On Windows/XAMPP, ensure the public/ folder exists and Apache has write access.');
                return 1;
            }
        }
        $copied = $this->copyDirectory($storageAppPublic, $publicStorage);
        $this->info("✓ Copied {$copied} files to public/storage.");

        $this->newLine();
        $this->warn('NOTE: You will need to run this command again after uploading new files.');
        $this->warn('Alternatively, add this to your deployment script or cron job.');

        return 0;
    }

    /**
     * Recursively copy files from source to destination.
     */
    private function copyDirectory(string $source, string $destination): int
    {
        $copied = 0;

        if (!is_dir($source)) {
            return 0;
        }

        $items = File::allFiles($source);
        foreach ($items as $item) {
            $relativePath = $item->getRelativePathname();
            $destPath = $destination . '/' . $relativePath;
            $destDir = dirname($destPath);

            if (!is_dir($destDir)) {
                mkdir($destDir, 0755, true);
            }

            if (copy($item->getPathname(), $destPath)) {
                chmod($destPath, 0644);
                $copied++;
            }
        }

        return $copied;
    }
}
