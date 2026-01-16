<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MangaReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'manga_id',
        'user_id',
        'chapter_slug',
        'content',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function manga()
    {
        return $this->belongsTo(MangaMetadata::class, 'manga_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
