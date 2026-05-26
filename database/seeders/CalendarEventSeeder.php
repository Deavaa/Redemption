<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\CalendarEvent;
use App\Models\Exam;
use Illuminate\Database\Seeder;

class CalendarEventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 1. Syncs existing exams to calendar events
     * 2. Seeds Ethiopian public holidays for the current academic year
     */
    public function run(): void
    {
        // ── 1. Sync existing exams → calendar events ────────────────────
        $this->syncExamsToCalendar();

        // ── 2. Seed Ethiopian holidays ──────────────────────────────────
        $this->seedEthiopianHolidays();
    }

    /**
     * Create calendar events for any exams that don't already have one.
     */
    private function syncExamsToCalendar(): void
    {
        $exams = Exam::all();
        $synced = 0;

        foreach ($exams as $exam) {
            // Skip if this exam already has a calendar event
            $exists = CalendarEvent::where('exam_id', $exam->id)->exists();
            if ($exists) {
                continue;
            }

            $typeLabel = ucfirst($exam->type);
            $title = "{$typeLabel}: {$exam->name}";

            // Build description
            $descParts = [];
            if ($exam->term) {
                $descParts[] = "Term: {$exam->term->name}";
            }
            if ($exam->total_marks) {
                $descParts[] = "Total Marks: {$exam->total_marks}";
            }
            if ($exam->classRoom) {
                $descParts[] = "Class: {$exam->classRoom->name}";
            }
            if ($exam->subject) {
                $descParts[] = "Subject: {$exam->subject->name}";
            }
            if ($exam->description) {
                $descParts[] = $exam->description;
            }

            CalendarEvent::create([
                'title'            => $title,
                'description'      => implode(' | ', $descParts),
                'category'         => 'exam',
                'color'            => CalendarEvent::categoryColors()['exam'] ?? '#f59e0b',
                'start_date'       => $exam->start_date,
                'end_date'         => $exam->end_date,
                'start_time'       => $exam->start_time ? substr($exam->start_time, 0, 5) : null,
                'end_time'         => $exam->end_time ? substr($exam->end_time, 0, 5) : null,
                'is_all_day'       => empty($exam->start_time),
                'is_announcement'   => true,
                'is_approved'      => true,
                'academic_year_id' => $exam->academic_year_id,
                'scope'            => 'school',
                'source_type'      => 'exam',
                'exam_id'          => $exam->id,
                'created_by'       => 1, // system/admin
            ]);

            $synced++;
        }

        $this->command->info("Synced {$synced} exams to calendar events.");
    }

    /**
     * Seed Ethiopian public holidays as calendar events.
     * Ethiopian holidays follow the Ethiopian calendar but are observed on
     * specific Gregorian dates each year. We seed holidays for 2017–2018 EC
     * (approx 2024–2026 GC).
     */
    private function seedEthiopianHolidays(): void
    {
        // Get current academic year
        $academicYear = AcademicYear::where('is_current', true)->first();
        if (!$academicYear) {
            $academicYear = AcademicYear::orderBy('id', 'desc')->first();
        }

        // Ethiopian holidays in Gregorian dates for 2025 and 2026
        // These dates shift slightly each year on the Gregorian calendar
        $holidays = $this->getEthiopianHolidays();

        $created = 0;
        foreach ($holidays as $holiday) {
            // Skip if this holiday already exists (check by title + start_date)
            $exists = CalendarEvent::where('title', $holiday['title'])
                ->where('start_date', $holiday['start_date'])
                ->exists();

            if ($exists) {
                continue;
            }

            CalendarEvent::create([
                'title'            => $holiday['title'],
                'description'      => $holiday['description'] ?? null,
                'category'         => 'holiday',
                'color'            => CalendarEvent::categoryColors()['holiday'] ?? '#ef4444',
                'start_date'       => $holiday['start_date'],
                'end_date'         => $holiday['end_date'] ?? $holiday['start_date'],
                'is_all_day'       => true,
                'is_announcement'   => true,
                'is_approved'      => true,
                'academic_year_id' => $academicYear?->id,
                'scope'            => 'school',
                'source_type'      => 'ethiopian_holiday',
                'created_by'       => 1,
            ]);

            $created++;
        }

        $this->command->info("Seeded {$created} Ethiopian holidays to calendar.");
    }

    /**
     * Returns an array of Ethiopian public holidays in Gregorian dates.
     * Ethiopian holidays are based on the Ethiopian calendar (EC).
     * Dates below are approximate Gregorian equivalents for 2025-2026.
     */
    private function getEthiopianHolidays(): array
    {
        return [
            // ── 2025 (2017 EC) ──────────────────────────────────────────
            [
                'title'       => 'Enkutatash (Ethiopian New Year)',
                'description' => 'Ethiopian New Year — 1 Meskerem 2017 EC. Marks the end of the rainy season and the beginning of the harvest.',
                'start_date'  => '2025-09-11',
            ],
            [
                'title'       => 'Meskel (Finding of the True Cross)',
                'description' => 'Celebrates the discovery of the True Cross upon which Jesus was crucified. Demera bonfire ceremony on the eve. 17 Meskerem 2017 EC.',
                'start_date'  => '2025-09-27',
            ],
            [
                'title'       => 'Irreechaa (Oromo Thanksgiving)',
                'description' => 'Oromo thanksgiving festival celebrated at Lake Hora Arsadi. Marks the end of the rainy season and beginning of spring.',
                'start_date'  => '2025-10-04',
            ],
            [
                'title'       => 'Genna (Ethiopian Christmas)',
                'description' => 'Ethiopian Orthodox Christmas — 29 Tahsas 2017 EC. Celebrated with church services, traditional games, and feasting.',
                'start_date'  => '2025-01-07',
            ],
            [
                'title'       => 'Timkat (Epiphany)',
                'description' => 'Ethiopian Orthodox celebration of the baptism of Jesus Christ in the Jordan River. 11 Tir 2017 EC. Features colorful processions and reenactments.',
                'start_date'  => '2025-01-19',
                'end_date'    => '2025-01-20',
            ],
            [
                'title'       => 'Adwa Victory Day',
                'description' => 'Commemorates the Ethiopian victory over Italian forces at the Battle of Adwa on March 1, 1896. A symbol of African resistance against colonialism.',
                'start_date'  => '2025-03-02',
            ],
            [
                'title'       => 'Fasika (Ethiopian Easter)',
                'description' => 'Ethiopian Orthodox Easter — the most important holiday in the Orthodox calendar. Celebrated after 55 days of Lent fasting.',
                'start_date'  => '2025-04-20',
            ],
            [
                'title'       => 'International Labour Day',
                'description' => 'Workers\' Day — public holiday honoring the labor movement.',
                'start_date'  => '2025-05-01',
            ],
            [
                'title'       => 'Derg Downfall Day',
                'description' => 'Commemorates the fall of the Derg military regime on May 28, 1991. Marks the beginning of the current federal republic.',
                'start_date'  => '2025-05-28',
            ],
            [
                'title'       => 'Eid al-Fitr',
                'description' => 'Islamic holiday marking the end of Ramadan. Date may vary based on moon sighting.',
                'start_date'  => '2025-03-31',
            ],
            [
                'title'       => 'Eid al-Adha',
                'description' => 'Islamic Festival of Sacrifice. Date may vary based on moon sighting.',
                'start_date'  => '2025-06-07',
            ],

            // ── 2026 (2018 EC) ──────────────────────────────────────────
            [
                'title'       => 'Genna (Ethiopian Christmas)',
                'description' => 'Ethiopian Orthodox Christmas — 29 Tahsas 2018 EC. Celebrated with church services, traditional games, and feasting.',
                'start_date'  => '2026-01-07',
            ],
            [
                'title'       => 'Timkat (Epiphany)',
                'description' => 'Ethiopian Orthodox celebration of the baptism of Jesus Christ in the Jordan River. 11 Tir 2018 EC. Features colorful processions and reenactments.',
                'start_date'  => '2026-01-19',
                'end_date'    => '2026-01-20',
            ],
            [
                'title'       => 'Adwa Victory Day',
                'description' => 'Commemorates the Ethiopian victory over Italian forces at the Battle of Adwa on March 1, 1896.',
                'start_date'  => '2026-03-02',
            ],
            [
                'title'       => 'Fasika (Ethiopian Easter)',
                'description' => 'Ethiopian Orthodox Easter — the most important holiday in the Orthodox calendar. Celebrated after 55 days of Lent fasting.',
                'start_date'  => '2026-04-12',
            ],
            [
                'title'       => 'International Labour Day',
                'description' => 'Workers\' Day — public holiday honoring the labor movement.',
                'start_date'  => '2026-05-01',
            ],
            [
                'title'       => 'Derg Downfall Day',
                'description' => 'Commemorates the fall of the Derg military regime on May 28, 1991.',
                'start_date'  => '2026-05-28',
            ],
            [
                'title'       => 'Enkutatash (Ethiopian New Year)',
                'description' => 'Ethiopian New Year — 1 Meskerem 2018 EC. Marks the end of the rainy season and the beginning of the harvest.',
                'start_date'  => '2026-09-11',
            ],
            [
                'title'       => 'Meskel (Finding of the True Cross)',
                'description' => 'Celebrates the discovery of the True Cross. Demera bonfire ceremony on the eve. 17 Meskerem 2018 EC.',
                'start_date'  => '2026-09-27',
            ],
        ];
    }
}
