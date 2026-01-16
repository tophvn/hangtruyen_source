<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MangaComment extends Model
{
    use HasFactory;

    protected $table = 'manga_comments';

    protected $fillable = [
        'manga_id',
        'chapter_id',
        'user_id',
        'parent_id',
        'content',
        'likes_count',
    ];

    public function manga()
    {
        return $this->belongsTo(MangaMetadata::class, 'manga_id');
    }

    public function chapter()
    {
        return $this->belongsTo(MangaChapter::class, 'chapter_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function parent()
    {
        return $this->belongsTo(MangaComment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(MangaComment::class, 'parent_id')->orderBy('created_at', 'asc');
    }

    public function likes()
    {
        return $this->hasMany(MangaCommentLike::class, 'comment_id');
    }

    public function isLikedBy($userId)
    {
        if (!$userId) {
            return false;
        }
        return $this->likes()->where('user_id', $userId)->exists();
    }

    public function incrementLikes()
    {
        $this->increment('likes_count');
    }

    public function decrementLikes()
    {
        if ($this->likes_count > 0) {
            $this->decrement('likes_count');
        }
    }
}
