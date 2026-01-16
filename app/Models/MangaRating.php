<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MangaRating extends Model
{
    use HasFactory;

    protected $table = 'manga_ratings';

    protected $fillable = [
        'manga_id',
        'user_id',
        'rating',
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
