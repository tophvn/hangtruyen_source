<div class="list-all-comments" style="display: flex; flex-direction: column; height: 100%;">
    <div class="offcanvas-header" style="flex-shrink: 0;">
        <button class="btn-close" type="button" data-bs-dismiss="offcanvas" aria-label="Close"><i class="icon-close-circle"></i></button>
        <h4 class="title">
            Bình luận <span class="countComment">({{ $commentsCount ?? 0 }})</span>
        </h4>
    </div>
    <div id="content-comments" style="flex: 1; overflow-y: auto; display: flex; flex-direction: column;">
        <div class="comment-input d-flex" style="flex-shrink: 0;">
            <div class="avatar-temp user-avatar-img avatar-temp-cmt">
                <img src="{{ asset('images/favicon.png') }}" style="object-fit:contain">
            </div>
            <div class="ci-form flex-grow-1">
                <div class="user-name cmt-noti">Bạn cần <a href="" class="color" rel="nofollow" data-bs-toggle="modal" data-bs-target="#loginModal">đăng nhập</a> để bình luận.</div>
                <form class="preform comment-form">
                    <div class="loading-absolute bg-white" style="display: none;">
                        <div class="loading">
                            <div class="span1"></div>
                            <div class="span2"></div>
                            <div class="span3"></div>
                        </div>
                    </div>
                    <div class="cmt-box">
                        <textarea class="form-control form-control-textarea" id="df-cm-content" name="content" maxlength="3000" placeholder="Bình luận" required=""></textarea>
                        <div class="ci-buttons align-items-center" id="df-cm-buttons">
                            <div class="ci-b-right d-flex align-items-center">
                                <button type="button" class="btn btn-cmt">Bình luận</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="list-comments" style="flex: 1; overflow-y: auto;">
            @if(isset($comments) && $comments->count() > 0)
                @foreach($comments as $comment)
                    @include('manga.components.comment-item', [
                        'comment' => $comment,
                        'isLiked' => isset($likedCommentIds) && is_array($likedCommentIds) && in_array($comment->id, $likedCommentIds),
                        'mangaSlug' => $mangaSlug ?? '',
                        'likedCommentIds' => $likedCommentIds ?? [],
                    ])
                @endforeach
            @endif
        </div>
        
        <!-- Template for new comments (hidden) -->
        <div class="template-comment" style="display: none;">
            <div class="cmt-line d-flex" data-parent-id="">
                <div class="user-avatar flex-shrink-0">
                    <div id="avatar-temp-hs" class="avatar-temp user-avatar-img" style="background-color: #787978" data-name=""></div>
                </div>
                <div class="info flex-grow-1">
                    <div class="ihead">
                        <div class="user-name"></div>
                        <div class="time"></div>
                    </div>
                    <div class="ibody">
                        <p class=""></p>
                    </div>
                    <div class="ibottom">
                        <div class="ib-li ib-reply">
                            <a href="#" class="btn-reply">
                                <i class="icon-messages"></i> Trả lời</a>
                        </div>
                        <div class="ib-li ib-like">
                            <span class="cm-btn-like">
                                <i class="icon-like"></i> Thích<span class="value">0</span>
                            </span>
                        </div>
                    </div>
                    <div class="reply-box">
                        <div class="cmt-line d-none"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
