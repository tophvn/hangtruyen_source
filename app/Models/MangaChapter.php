<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MangaChapter extends Model
{
    use HasFactory;

    protected $table = 'manga_chapters';

    protected $fillable = [
        'manga_id',
        'chapter_slug',
        'chapter_name',
        'chapter_api_id',
        'views_count',
        'first_viewed_at',
        'last_viewed_at',
    ];

    protected $casts = [
        'first_viewed_at' => 'datetime',
        'last_viewed_at' => 'datetime',
    ];

    public function manga()
    {
        return $this->belongsTo(MangaMetadata::class, 'manga_id');
    }

    public function incrementViews()
    {
        $this->increment('views_count');
        if (!$this->first_viewed_at) {
            $this->first_viewed_at = now();
        }
        $this->last_viewed_at = now();
        $this->save();
    }
}
