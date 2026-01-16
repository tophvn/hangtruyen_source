<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MangaReadingHistory extends Model
{
    use HasFactory;

    protected $table = 'manga_reading_history';

    protected $fillable = [
        'user_id',
        'manga_id',
        'chapter_id',
        'chapter_slug',
        'last_read_at',
    ];

    protected $casts = [
        'last_read_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function manga()
    {
        return $this->belongsTo(MangaMetadata::class, 'manga_id');
    }

    public function chapter()
    {
        return $this->belongsTo(MangaChapter::class, 'chapter_id');
    }
}
