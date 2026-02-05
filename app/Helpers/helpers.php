<?php

use Carbon\Carbon;

if (!function_exists('formatVietnameseTime')) {
    function formatVietnameseTime($dateTime)
    {
        if (!$dateTime) {
            return null;
        }

        try {
            $date = Carbon::parse($dateTime);
            $now = Carbon::now();
            $diffInSeconds = $now->diffInSeconds($date);
            $diffInMinutes = $now->diffInMinutes($date);
            $diffInHours = $now->diffInHours($date);
            $diffInDays = $now->diffInDays($date);
            $diffInMonths = $now->diffInMonths($date);
            $diffInYears = $now->diffInYears($date);

            if ($diffInSeconds < 60) {
                return 'vừa xong';
            } elseif ($diffInMinutes < 60) {
                return $diffInMinutes . ' phút trước';
            } elseif ($diffInHours < 24) {
                return $diffInHours . ' giờ trước';
            } elseif ($diffInDays < 30) {
                return $diffInDays . ' ngày trước';
            } elseif ($diffInMonths < 12) {
                return $diffInMonths . ' tháng trước';
            } else {
                return $diffInYears . ' năm trước';
            }
        } catch (\Exception $e) {
            return null;
        }
    }
}

if (!function_exists('formatChapterNameForDisplay')) {
    function formatChapterNameForDisplay($chapterName)
    {
        if (empty($chapterName)) {
            return 'Chapter 0';
        }

        if (preg_match('/^Chapter\s+/i', $chapterName)) {
            return $chapterName;
        }

        if (is_numeric($chapterName)) {
            return 'Chapter ' . $chapterName;
        }

        return $chapterName;
    }
}
if (!function_exists('proxyImageUrl')) {
    function proxyImageUrl($url)
    {
        if (empty($url)) {
            return asset('images/pre-load1.png');
        }

        return $url;
    }
}
