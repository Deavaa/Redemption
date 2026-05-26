<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Setting;

return new class extends Migration
{
    public function up(): void
    {
        $settings = [
            [
                'key' => 'about_image',
                'value' => '',
                'group' => 'about',
                'type' => 'file',
                'description' => 'Image displayed on the About page',
            ],
            [
                'key' => 'about_students_count',
                'value' => '500+',
                'group' => 'about',
                'type' => 'text',
                'description' => 'Number of students shown on About page (e.g. "500+")',
            ],
            [
                'key' => 'about_years_experience',
                'value' => '15+',
                'group' => 'about',
                'type' => 'text',
                'description' => 'Years of experience shown on About page (e.g. "15+")',
            ],
            [
                'key' => 'about_programs',
                'value' => '8',
                'group' => 'about',
                'type' => 'text',
                'description' => 'Number of programs shown on About page (e.g. "8")',
            ],
            [
                'key' => 'about_success_rate',
                'value' => '95%',
                'group' => 'about',
                'type' => 'text',
                'description' => 'Success rate shown on About page (e.g. "95%")',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }

    public function down(): void
    {
        Setting::whereIn('key', [
            'about_image',
            'about_students_count',
            'about_years_experience',
            'about_programs',
            'about_success_rate',
        ])->delete();
    }
};
