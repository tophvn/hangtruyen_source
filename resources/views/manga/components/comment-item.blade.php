@php
    $user = $comment->user;
    $chapter = $comment->chapter;
    $avatar = $user->avatar ?? null;
    $userName = $user->name ?? 'Người dùng';
    $firstLetter = $userName ? strtoupper(mb_substr($userName, 0, 1)) : '?';
    $replies = $comment->replies;
    $repliesCount = $replies->count();
@endphp
<div class="cmt-line d-flex" data-parent-id="{{ $comment->id }}" id="cmt-{{ $comment->id }}">
    <div class="user-avatar flex-shrink-0">
        <div id="avatar-temp-hs" class="avatar-temp user-avatar-img" 
            @if($avatar)
                style="background-image: url({{ $avatar }}); background-size: cover; background-position: center; background-repeat: no-repeat; background-color: #787978"
            @else
                style="background-color: #787978"
            @endif
            data-name="{{ $userName }}">
            @if(!$avatar)
                {{ $firstLetter }}
            @endif
        </div>
    </div>
    <div class="info flex-grow-1">
        <div class="ihead">
            <div class="user-name">{{ $userName }}</div>
            
            @if($chapter)
                <a href="/truyen-tranh/{{ $mangaSlug }}/{{ $chapter->chapter_slug }}" class="link-cmt-chap">
                    Chapter {{ $chapter->chapter_name }}
                </a>
            @endif
            
            <div class="time">{{ $comment->created_at->diffForHumans() }}</div>
        </div>
        <div class="ibody">
            <p class="">{{ $comment->content }}</p>
        </div>
        <div class="ibottom">
            <div class="ib-li ib-reply">
                <a href="#" class="btn-reply">
                    <i class="icon-messages"></i> Trả lời</a>
            </div>
            <div class="ib-li ib-like">
                <span class="cm-btn-like">
                    <i class="icon-{{ $isLiked ? 'liked' : 'like' }}"></i> Thích<span class="value">{{ $comment->likes_count }}</span>
                </span>
            </div>
        </div>
        
        @if($repliesCount > 0)
            <div class="replies" id="block-reply-{{ $comment->id }}">
                <div class="rep-more rep-in">
                    <a class="cm-btn-show-rep" data-bs-toggle="collapse" href="#collapseReply{{ $comment->id }}" role="button" aria-expanded="true" aria-controls="collapseReply{{ $comment->id }}">
                        <ion-icon name="caret-down"></ion-icon><span>{{ $repliesCount }} {{ $repliesCount == 1 ? 'câu trả lời' : 'câu trả lời' }}</span>
                    </a>
                </div>
                <div class="replies-wrap collapse show" id="collapseReply{{ $comment->id }}">
                    @foreach($replies as $reply)
                        @php
                            $replyUser = $reply->user;
                            $replyAvatar = $replyUser->avatar ?? null;
                            $replyUserName = $replyUser->name ?? 'Người dùng';
                            $replyFirstLetter = $replyUserName ? strtoupper(mb_substr($replyUserName, 0, 1)) : '?';
                                $replyIsLiked = isset($likedCommentIds) && is_array($likedCommentIds) && in_array($reply->id, $likedCommentIds);
                            $replyChapter = $reply->chapter;
                        @endphp
                        <div class="cmt-line d-flex" id="cmt-{{ $reply->id }}" data-parent-id="{{ $comment->id }}">
                            <div class="user-avatar flex-shrink-0">
                                <div id="avatar-temp-hs" class="avatar-temp user-avatar-img" 
                                    @if($replyAvatar)
                                        style="background-image: url({{ $replyAvatar }}); background-size: cover; background-position: center; background-repeat: no-repeat; background-color: #787978"
                                    @else
                                        style="background-color: #787978"
                                    @endif
                                    data-name="{{ $replyUserName }}">
                                    @if(!$replyAvatar)
                                        {{ $replyFirstLetter }}
                                    @endif
                                </div>
                            </div>
                            <div class="info flex-grow-1">
                                <div class="ihead">
                                    <div class="user-name">{{ $replyUserName }}</div>
                                    <div class="time">{{ $reply->created_at->diffForHumans() }}</div>
                                </div>
                                <div class="ibody">
                                    <p class="">{{ $reply->content }}</p>
                                </div>
                                <div class="ibottom">
                                    <div class="ib-li ib-reply">
                                        <a href="" class="btn-reply">
                                            <i class="icon-messages"></i>Trả lời</a>
                                    </div>
                                    <div class="ib-li ib-like">
                                        <a class="cm-btn-like" data-type="1">
                                            <i class="icon-{{ $replyIsLiked ? 'liked' : 'like' }}"></i><span class="value">{{ $reply->likes_count }}</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
        
        <div class="reply-box">
            <div class="cmt-line d-none"></div>
        </div>
    </div>
</div>
