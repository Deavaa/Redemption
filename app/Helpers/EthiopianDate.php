<?php

namespace App\Helpers;

class EthiopianDate
{
    /**
     * Ethiopian month names
     */
    private static $monthNames = [
        'መስከረም', 'ጥቅምት', 'ኅዳር', 'ታኅሣሥ',
        'ጥር', 'የካቲት', 'መጋቢት', 'ሚያዝያ',
        'ግንቦት', 'ሰኔ', 'ሐምሌ', 'ነሐሴ', 'ጳጉሜ'
    ];

    private static $monthNamesLatin = [
        'Meskerem', 'Tikimt', 'Hidar', 'Tahsas',
        'Tir', 'Yekatit', 'Megabit', 'Miyazia',
        'Ginbot', 'Sene', 'Hamle', 'Nehase', 'Pagume'
    ];

    /**
     * Days in each Ethiopian month (Pagume has 5 or 6 days)
     */
    private static $daysInMonth = [30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 5];

    /**
     * Convert Gregorian date to Ethiopian date
     * @param string $gregorianDate Date in Y-m-d format
     * @return array ['year' => int, 'month' => int, 'day' => int, 'formatted' => string]
     */
    public static function fromGregorian($gregorianDate)
    {
        $timestamp = strtotime($gregorianDate);
        $gYear = date('Y', $timestamp);
        $gMonth = date('n', $timestamp);
        $gDay = date('j', $timestamp);

        // Calculate the Ethiopian year
        // Ethiopian New Year falls on September 11 (Gregorian) in most years
        // and September 12 in the year before Gregorian leap year
        
        $ethiopianYear = $gYear - 8;
        
        // Determine if this date is before or after Ethiopian New Year
        // Ethiopian New Year is September 11 (or 12 in Gregorian leap year eve)
        $isGregorianLeapYearEve = ($gYear % 4 == 3); // Year before leap year
        $ethiopianNewYearDay = $isGregorianLeapYearEve ? 12 : 11;
        
        // Check if the date is before Ethiopian New Year
        if ($gMonth < 9 || ($gMonth == 9 && $gDay < $ethiopianNewYearDay)) {
            $ethiopianYear = $ethiopianYear - 1;
        }
        
        // Calculate days from Ethiopian New Year
        $gregorianMonthDays = [0, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
        if ($gYear % 4 == 0) $gregorianMonthDays[2] = 29; // Gregorian leap year
        
        // Day of year for the given date
        $dayOfYear = $gDay;
        for ($m = 1; $m < $gMonth; $m++) {
            $dayOfYear += $gregorianMonthDays[$m];
        }
        
        // Day of year for Ethiopian New Year
        $newYearDayOfYear = $ethiopianNewYearDay;
        for ($m = 1; $m < 9; $m++) {
            $newYearDayOfYear += $gregorianMonthDays[$m];
        }
        
        // Days since Ethiopian New Year
        $daysSinceNewYear = $dayOfYear - $newYearDayOfYear;
        if ($daysSinceNewYear < 0) {
            // Date is before Ethiopian New Year, so we're in the previous Ethiopian year
            // Calculate days from previous Ethiopian New Year
            $prevYearDays = $isGregorianLeapYearEve ? 366 : 365;
            $daysSinceNewYear = $prevYearDays + $daysSinceNewYear;
        }
        
        // Convert to Ethiopian month and day
        $ethiopianMonth = 1;
        $ethiopianDay = $daysSinceNewYear + 1;
        
        while ($ethiopianDay > self::$daysInMonth[$ethiopianMonth - 1]) {
            $ethiopianDay -= self::$daysInMonth[$ethiopianMonth - 1];
            $ethiopianMonth++;
        }
        
        // Check if Ethiopian year is a leap year (Pagume has 6 days)
        // Ethiopian leap year occurs every 4 years
        $isEthiopianLeapYear = (($ethiopianYear + 1) % 4 == 0);
        if ($ethiopianMonth == 13 && $isEthiopianLeapYear) {
            // Pagume can have 6 days in leap year
            self::$daysInMonth[12] = 6;
        } else {
            self::$daysInMonth[12] = 5;
        }
        
        $monthName = self::$monthNames[$ethiopianMonth - 1];
        
        $monthNameLatin = self::$monthNamesLatin[$ethiopianMonth - 1];

        return [
            'year' => $ethiopianYear,
            'month' => $ethiopianMonth,
            'day' => $ethiopianDay,
            'month_name' => $monthName,
            'month_name_latin' => $monthNameLatin,
            'formatted' => "{$ethiopianDay} {$monthName}, {$ethiopianYear}",
            'formatted_latin' => "{$ethiopianDay} {$monthNameLatin}, {$ethiopianYear}"
        ];
    }
    
    /**
     * Get Ethiopian date string from Gregorian date
     * @param string $gregorianDate
     * @return string
     */
    public static function format($gregorianDate)
    {
        $ethiopian = self::fromGregorian($gregorianDate);
        return $ethiopian['formatted'];
    }
    
    /**
     * Get month name by number
     * @param int $month
     * @return string
     */
    public static function getMonthName($month)
    {
        return self::$monthNames[$month - 1] ?? 'ጳጉሜ';
    }

    public static function getMonthNameLatin($month)
    {
        return self::$monthNamesLatin[$month - 1] ?? 'Pagume';
    }
}