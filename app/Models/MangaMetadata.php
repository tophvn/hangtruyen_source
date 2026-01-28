<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MangaMetadata extends Model
{
    use HasFactory;

    protected $table = 'manga_metadata';

    protected $fillable = [
        'slug',
        'source_type',
        'source_identifier',
        'title',
        'origin_name',
        'description',
        'cover_url',
        'panorama_url',
        'author',
        'status',
        'tags',
        'views_count',
        'rating',
        'chapters_count',
        'last_chapter_number',
        'last_synced_at',
        'is_active',
    ];

    protected $casts = [
        'tags' => 'array',
        'origin_name' => 'array',
        'last_synced_at' => 'datetime',
    ];

    public function getCoverUrlAttribute($value)
    {
        return proxyImageUrl($value);
    }

    public function chapters()
    {
        return $this->hasMany(MangaChapter::class, 'manga_id');
    }

    public function updateViewsCount()
    {
        $totalViews = $this->chapters()->sum('views_count');
        if ($this->views_count != $totalViews) {
            $this->views_count = $totalViews;
            $this->save();
        }
    }

    public function ratings()
    {
        return $this->hasMany(MangaRating::class, 'manga_id');
    }

    public function updateRating()
    {
        $avgRating = $this->ratings()->avg('rating');
        $this->rating = $avgRating ? round($avgRating, 2) : 0;
        $this->save();
        return $this->rating;
    }

    public function getUserRating($userId)
    {
        if (!$userId) {
            return null;
        }
        $rating = $this->ratings()->where('user_id', $userId)->first();
        return $rating ? $rating->rating : null;
    }

    public function follows()
    {
        return $this->hasMany(MangaFollow::class, 'manga_id');
    }

    public function isFollowedBy($userId)
    {
        if (!$userId) {
            return false;
        }
        return $this->follows()->where('user_id', $userId)->exists();
    }

    public function getFollowsCount()
    {
        return $this->follows()->count();
    }
}
