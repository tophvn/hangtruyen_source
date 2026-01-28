<?php

namespace App\Http\Controllers;

use App\Models\MangaMetadata;
use App\Models\MangaComment;
use App\Models\MangaCommentLike;
use App\Models\MangaChapter;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index($slug)
    {
        try {
            $mangaMetadata = MangaMetadata::where('slug', $slug)->first();

            if (!$mangaMetadata) {
                return response()->json(['status' => 'error', 'message' => 'Truyện không tồn tại'], 404);
            }

            $request = request();
            $chapterId = $request->input('chapter_id');
            $page = max(1, (int) $request->input('page', 1));
            $perPage = 20;
            $orderBy = $request->input('order', 'latest');

            $query = MangaComment::where('manga_id', $mangaMetadata->id)
                ->whereNull('parent_id');

            if ($chapterId) {
                $query->where('chapter_id', $chapterId);
            }

            if ($orderBy === 'oldest') {
                $query->orderBy('created_at', 'asc');
            } elseif ($orderBy === 'most_liked') {
                $query->orderBy('likes_count', 'desc')->orderBy('created_at', 'desc');
            } else {
                $query->orderBy('created_at', 'desc');
            }

            $comments = $query->with(['user', 'chapter', 'replies.user', 'replies.chapter'])
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get();

            $total = $query->count();
            $totalPages = ceil($total / $perPage);

            $userId = auth()->id();
            $likedCommentIds = [];
            if ($userId) {
                $commentIds = $comments->pluck('id')->toArray();
                $replyIds = [];
                foreach ($comments as $comment) {
                    $replyIds = array_merge($replyIds, $comment->replies->pluck('id')->toArray());
                }
                $allCommentIds = array_merge($commentIds, $replyIds);

                if (!empty($allCommentIds)) {
                    $likedCommentIds = MangaCommentLike::whereIn('comment_id', $allCommentIds)
                        ->where('user_id', $userId)
                        ->pluck('comment_id')
                        ->toArray();
                }
            }

            $html = '';
            foreach ($comments as $comment) {
                $html .= view('manga.components.comment-item', [
                    'comment' => $comment,
                    'isLiked' => in_array($comment->id, $likedCommentIds),
                    'mangaSlug' => $slug,
                    'likedCommentIds' => $likedCommentIds,
                ])->render();
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'html' => $html,
                    'page' => $page,
                    'total_pages' => $totalPages,
                    'total' => $total,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Có lỗi xảy ra khi tải bình luận'
            ], 500);
        }
    }

    public function store(Request $request, $slug)
    {
        try {
            if (!auth()->check()) {
                return response()->json(['status' => 'error', 'message' => 'Vui lòng đăng nhập'], 401);
            }

            $mangaMetadata = MangaMetadata::where('slug', $slug)->first();

            if (!$mangaMetadata) {
                return response()->json(['status' => 'error', 'message' => 'Truyện không tồn tại'], 404);
            }

            $content = $request->input('content');
            $chapterId = $request->input('chapter_id');
            $parentId = $request->input('parent_id');

            if (empty($content) || strlen(trim($content)) === 0) {
                return response()->json(['status' => 'error', 'message' => 'Nội dung bình luận không được để trống'], 400);
            }

            if (strlen($content) > 3000) {
                return response()->json(['status' => 'error', 'message' => 'Nội dung bình luận không được vượt quá 3000 ký tự'], 400);
            }

            $chapter = null;
            if ($chapterId) {
                $chapter = MangaChapter::where('id', $chapterId)
                    ->where('manga_id', $mangaMetadata->id)
                    ->first();
                if (!$chapter) {
                    return response()->json(['status' => 'error', 'message' => 'Chapter không tồn tại'], 404);
                }
            }

            if ($parentId) {
                $parentComment = MangaComment::where('id', $parentId)
                    ->where('manga_id', $mangaMetadata->id)
                    ->first();
                if (!$parentComment) {
                    return response()->json(['status' => 'error', 'message' => 'Bình luận cha không tồn tại'], 404);
                }
            }

            $comment = MangaComment::create([
                'manga_id' => $mangaMetadata->id,
                'chapter_id' => $chapter ? $chapter->id : null,
                'user_id' => auth()->id(),
                'parent_id' => $parentId,
                'content' => trim($content),
            ]);

            $comment->load(['user', 'chapter']);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $comment->id,
                    'content' => $comment->content,
                    'user' => [
                        'id' => $comment->user->id,
                        'name' => $comment->user->name,
                        'avatar' => $comment->user->avatar ?? null,
                    ],
                    'chapter' => $comment->chapter ? [
                        'id' => $comment->chapter->id,
                        'slug' => $comment->chapter->chapter_slug,
                        'name' => $comment->chapter->chapter_name,
                    ] : null,
                    'created_at' => $comment->created_at->diffForHumans(),
                    'likes_count' => 0,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Có lỗi xảy ra khi đăng bình luận'
            ], 500);
        }
    }

    public function like($slug, $commentId)
    {
        try {
            if (!auth()->check()) {
                return response()->json(['status' => 'error', 'message' => 'Vui lòng đăng nhập'], 401);
            }

            $mangaMetadata = MangaMetadata::where('slug', $slug)->first();

            if (!$mangaMetadata) {
                return response()->json(['status' => 'error', 'message' => 'Truyện không tồn tại'], 404);
            }

            $comment = MangaComment::where('id', $commentId)
                ->where('manga_id', $mangaMetadata->id)
                ->first();

            if (!$comment) {
                return response()->json(['status' => 'error', 'message' => 'Bình luận không tồn tại'], 404);
            }

            $userId = auth()->id();
            $like = MangaCommentLike::where('comment_id', $commentId)
                ->where('user_id', $userId)
                ->first();

            if ($like) {
                $like->delete();
                $comment->decrementLikes();
                $isLiked = false;
            } else {
                MangaCommentLike::create([
                    'comment_id' => $commentId,
                    'user_id' => $userId,
                ]);
                $comment->incrementLikes();
                $isLiked = true;
            }

            $comment->refresh();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'isLiked' => $isLiked,
                    'totalLike' => $comment->likes_count,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Có lỗi xảy ra khi thích bình luận'
            ], 500);
        }
    }

    public function getLikedIds($slug)
    {
        try {
            if (!auth()->check()) {
                return response()->json(['status' => 'success', 'data' => []]);
            }

            $mangaMetadata = MangaMetadata::where('slug', $slug)->first();

            if (!$mangaMetadata) {
                return response()->json(['status' => 'error', 'message' => 'Truyện không tồn tại'], 404);
            }

            $commentIds = MangaComment::where('manga_id', $mangaMetadata->id)
                ->pluck('id')
                ->toArray();

            $likedCommentIds = MangaCommentLike::whereIn('comment_id', $commentIds)
                ->where('user_id', auth()->id())
                ->pluck('comment_id')
                ->toArray();

            return response()->json([
                'status' => 'success',
                'data' => $likedCommentIds
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Có lỗi xảy ra'
            ], 500);
        }
    }

    public function getMangaId($commentId)
    {
        $comment = MangaComment::with('manga')->find($commentId);
        if (!$comment) {
            return response()->json(['error' => 'Comment not found'], 404);
        }

        return response()->json([
            'comment_id' => $comment->id,
            'manga_id' => $comment->manga_id,
            'manga_slug' => $comment->manga ? $comment->manga->slug : null,
        ]);
    }
}
