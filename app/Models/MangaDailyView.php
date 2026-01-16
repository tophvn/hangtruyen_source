<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MangaDailyView extends Model
{
    use HasFactory;

    protected $table = 'manga_daily_views';

    protected $fillable = [
        'manga_id',
        'view_date',
        'views_count',
    ];

    protected $casts = [
        'view_date' => 'date',
    ];

    public function manga()
    {
        return $this->belongsTo(MangaMetadata::class, 'manga_id');
    }

    /**
     * Tăng views cho manga trong ngày hiện tại
     */
    public static function incrementTodayViews($mangaId)
    {
        $today = now()->toDateString();
        
        $dailyView = self::firstOrCreate(
            [
                'manga_id' => $mangaId,
                'view_date' => $today,
            ],
            [
                'views_count' => 0,
            ]
        );
        
        $dailyView->increment('views_count');
        
        return $dailyView;
    }

    /**
     * Lấy tổng views trong khoảng thời gian
     */
    public static function getTotalViewsInPeriod($mangaId, $startDate, $endDate)
    {
        return self::where('manga_id', $mangaId)
            ->whereBetween('view_date', [$startDate, $endDate])
            ->sum('views_count');
    }
}
