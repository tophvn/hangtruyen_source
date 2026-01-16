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
