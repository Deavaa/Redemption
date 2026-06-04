<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AppController extends Controller
{
    /**
     * Serve the dynamic PWA manifest.webmanifest
     * This replaces the static manifest.json so paths work in all environments (local XAMPP and production)
     */
    public function manifest(): JsonResponse
    {
        $baseUrl = url('/');
        
        $manifest = [
            'name' => 'School of Redemption - School Management System',
            'short_name' => 'Redemption',
            'description' => 'Complete school management system for School of Redemption - Access marks, attendance, finance, and more',
            'start_url' => url('/login'),
            'display' => 'standalone',
            'orientation' => 'portrait-primary',
            'background_color' => '#0f172a',
            'theme_color' => '#6366f1',
            'scope' => url('/'),
            'icons' => [
                ['src' => asset('icons/icon-72x72.png'), 'sizes' => '72x72', 'type' => 'image/png', 'purpose' => 'any maskable'],
                ['src' => asset('icons/icon-96x96.png'), 'sizes' => '96x96', 'type' => 'image/png', 'purpose' => 'any maskable'],
                ['src' => asset('icons/icon-128x128.png'), 'sizes' => '128x128', 'type' => 'image/png', 'purpose' => 'any maskable'],
                ['src' => asset('icons/icon-144x144.png'), 'sizes' => '144x144', 'type' => 'image/png', 'purpose' => 'any maskable'],
                ['src' => asset('icons/icon-152x152.png'), 'sizes' => '152x152', 'type' => 'image/png', 'purpose' => 'any maskable'],
                ['src' => asset('icons/icon-192x192.png'), 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any maskable'],
                ['src' => asset('icons/icon-384x384.png'), 'sizes' => '384x384', 'type' => 'image/png', 'purpose' => 'any maskable'],
                ['src' => asset('icons/icon-512x512.png'), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any maskable'],
            ],
            'categories' => ['education', 'productivity'],
            'shortcuts' => [
                [
                    'name' => 'Mark Entry',
                    'short_name' => 'Marks',
                    'url' => url('/admin/mark-entries'),
                    'icons' => [['src' => asset('icons/icon-96x96.png'), 'sizes' => '96x96']],
                ],
                [
                    'name' => 'Attendance',
                    'short_name' => 'Attend.',
                    'url' => url('/admin/attendance'),
                    'icons' => [['src' => asset('icons/icon-96x96.png'), 'sizes' => '96x96']],
                ],
                [
                    'name' => 'Students',
                    'short_name' => 'Students',
                    'url' => url('/admin/students'),
                    'icons' => [['src' => asset('icons/icon-96x96.png'), 'sizes' => '96x96']],
                ],
            ],
            'prefer_related_applications' => false,
            'screenshots' => [
                [
                    'src' => asset('icons/icon-512x512.png'),
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'form_factor' => 'narrow',
                    'label' => 'Redemption School Management',
                ],
            ],
        ];

        return response()->json($manifest)
            ->header('Content-Type', 'application/manifest+json')
            ->header('Cache-Control', 'no-cache, must-revalidate');
    }

    /**
     * Show the app download/install page
     */
    public function download()
    {
        $settings = \App\Models\Setting::pluck('value', 'key')->toArray();
        return view('app.download', compact('settings'));
    }

    /**
     * Download the Android APK file
     */
    public function downloadApk()
    {
        // Try storage/app/public/downloads first, then public/downloads
        $paths = [
            storage_path('app/public/downloads/SchoolOfRedemption.apk'),
            public_path('downloads/SchoolOfRedemption.apk'),
        ];

        foreach ($paths as $path) {
            if (file_exists($path)) {
                return response()->download($path, 'SchoolOfRedemption.apk', [
                    'Content-Type' => 'application/vnd.android.package-archive',
                    'Content-Length' => filesize($path),
                ]);
            }
        }

        abort(404, 'APK file not found. Please contact the administrator.');
    }

    /**
     * Download the Training Android APK file
     */
    public function downloadTrainingApk()
    {
        $paths = [
            storage_path('app/public/downloads/RedemptionTraining.apk'),
            public_path('downloads/RedemptionTraining.apk'),
        ];

        foreach ($paths as $path) {
            if (file_exists($path)) {
                return response()->download($path, 'RedemptionTraining.apk', [
                    'Content-Type' => 'application/vnd.android.package-archive',
                    'Content-Length' => filesize($path),
                ]);
            }
        }

        abort(404, 'Training APK file not found. Please contact the administrator.');
    }
}
