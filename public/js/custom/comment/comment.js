var listCommentsElem = $('.list-comments');
var templateCommentElem = $('.template-comment').children();
$(document).off('click', '.btn-cmt').on(
    'click',
    '.btn-cmt',
    debounce(async function () {
        if (!isLogin()) {
            alert('Bạn cần đăng nhập');
            return;
        }
        let userStorage = sessionStorage.getItem('user');

        var commentTextareaElem = $(this).closest('form').find('textarea');
        const content = commentTextareaElem.val();
        const user = JSON.parse(userStorage);
        if (!content) {
            alert('Không để trống bình luận');
        } else {
            let parentCommentId = null;
            const parentCommentElem = $(this)
                .closest('.reply-box')
                .closest('.cmt-line');
            if (parentCommentElem.length) {
                parentCommentId = parentCommentElem.attr('data-parent-id');
            }

            const $btn = $(this);
            if ($btn.hasClass('processing')) {
                return;
            }
            $btn.addClass('disabled processing');

            const response = await postComment(
                mangaDetail.id,
                chapterDetail ? chapterDetail.id : null,
                content,
                parentCommentId,
            );

            $btn.removeClass('processing');

            if (response) {
                const commentId = response.id;
                
                if ($(`#cmt-${commentId}`).length > 0) {
                    $btn.removeClass('disabled');
                    return;
                }
                
                const newComment = templateCommentElem.clone(false, true);
                commentTextareaElem.val('');
                newComment.attr('id', `cmt-${commentId}`);
                newComment.attr('data-parent-id', commentId);
                
                newComment.find('.user-name').text(user.name);
                
                const avatarElem = newComment.find('.user-avatar-img');
                if (user.avatar) {
                    avatarElem
                        .text('')
                        .css('background-image', `url(${user.avatar})`)
                        .css('background-size', 'cover')
                        .css('background-position', 'center')
                        .css('background-repeat', 'no-repeat')
                        .css('background-color', '#787978');
                } else {
                    const firstLetter = user.name ? user.name[0].toUpperCase() : '?';
                    avatarElem
                        .text(firstLetter)
                        .css('background-color', '#787978')
                        .css('background-image', '');
                }
                avatarElem.attr('data-name', user.name);

                newComment.find('.ibody').children('p').text(response.content);
                newComment.find('.time').text(response.created_at || 'Vừa xong');
                newComment.find('.value').text(response.likes_count || 0);
                
                if (response.chapter) {
                    const chapterLink = $(`<a href="/truyen-tranh/${mangaDetail.rawSlug}/${response.chapter.slug}" class="link-cmt-chap">Chapter ${response.chapter.name}</a>`);
                    const ihead = newComment.find('.ihead');
                    const timeElem = newComment.find('.time');
                    timeElem.before(chapterLink);
                }

                if (parentCommentId) {
                    newComment.attr('data-parent-id', parentCommentId);
                    let repliesElem = parentCommentElem.find('.replies');
                    if (repliesElem.length) {
                        repliesElem
                            .children('.replies-wrap')
                            .append(newComment);
                        
                        const repMore = repliesElem.find('.rep-more');
                        if (repMore.length) {
                            const currentCount = parseInt(repMore.find('span').text().match(/\d+/)?.[0] || '0');
                            repMore.find('span').html(`${currentCount + 1} ${currentCount + 1 === 1 ? 'câu trả lời' : 'câu trả lời'}`);
                        } else {
                            const repliesHtml = `
                                <div class="replies" id="block-reply-${parentCommentId}">
                                    <div class="rep-more rep-in">
                                        <a class="cm-btn-show-rep" data-bs-toggle="collapse" href="#collapseReply${parentCommentId}" role="button" aria-expanded="true" aria-controls="collapseReply${parentCommentId}">
                                            <ion-icon name="caret-down"></ion-icon><span>1 câu trả lời</span>
                                        </a>
                                    </div>
                                    <div class="replies-wrap collapse show" id="collapseReply${parentCommentId}">
                                    </div>
                                </div>
                            `;
                            const repliesSection = $(repliesHtml);
                            repliesSection.find('.replies-wrap').append(newComment);
                            parentCommentElem.find('.ibottom').after(repliesSection);
                        }
                    } else {
                        repliesElem = $('<div />')
                            .addClass('replies')
                            .attr('id', `block-reply-${parentCommentId}`)
                            .append(
                                $('<div />')
                                    .addClass('rep-more rep-in')
                                    .append(
                                        $(`<a class="cm-btn-show-rep" data-bs-toggle="collapse" href="#collapseReply${parentCommentId}" role="button" aria-expanded="true" aria-controls="collapseReply${parentCommentId}">
                                            <ion-icon name="caret-down"></ion-icon><span>1 câu trả lời</span>
                                        </a>`)
                                    ),
                                $('<div />')
                                    .addClass('replies-wrap collapse show')
                                    .attr('id', `collapseReply${parentCommentId}`)
                                    .append(newComment),
                            );
                        parentCommentElem.find('.ibottom').after(repliesElem);
                    }
                } else {
                    listCommentsElem.prepend(newComment);
                }
                
                const countElem = $('.countComment');
                if (countElem.length) {
                    const currentCount = parseInt(countElem.text().match(/\d+/)?.[0] || '0');
                    if (!parentCommentId) {
                        countElem.text(`(${currentCount + 1})`);
                    }
                }
            }

            $btn.removeClass('disabled');
        }
    }, 150),
);

// Pagination
var currentPage = 1;
var countPage = $('.pagination').attr('data-count-page');
listCommentsElem.on('click', '.pagination > li', async function (e) {
    e.preventDefault();
    if ($(this).hasClass('active')) {
        return;
    }
    let dataPage = parseInt($(this).attr('data-page'));
    const isNextPage = !dataPage;
    const isPrevPage = dataPage == -1;
    if (isNextPage && countPage <= currentPage) {
        return;
    }

    if (isPrevPage && currentPage <= 1) {
        return;
    }

    currentPage = isNextPage
        ? currentPage + 1
        : isPrevPage
          ? currentPage - 1
          : dataPage;
    let order = $('.sort-comments a.dropdown-toggle').data('value');

    const comments = await getComment(
        mangaDetail.id,
        chapterDetail ? chapterDetail.id : null,
        currentPage,
        null,
        order,
    );

    listCommentsElem.empty();
    listCommentsElem.append(comments);
});

$('.dropdown-sort .dropdown-item').on('click', async function () {
    $('.sort-comments a')
        .contents()
        .filter(function () {
            return this.nodeType === 3;
        })
        .first()
        .replaceWith('<span>' + $(this).text() + '</span>');

    $('.sort-comments a.dropdown-toggle').attr(
        'data-value',
        $(this).data('value'),
    );

    let order = $(this).data('value');

    const comments = await getComment(
        mangaDetail.id,
        chapterDetail ? chapterDetail.id : null,
        parseInt($(this).attr('data-page')),
        null,
        order,
    );

    listCommentsElem.empty();
    listCommentsElem.append(comments);

    currentPage = 1;
    $(pageElem).find('li').removeClass('active');
    $(pageElem)
        .children('[data-page="' + currentPage + '"]')
        .addClass('active');
});

function handleCheckLoginCommentInput() {
    if (!isLogin()) {
        // $('#user-ava-cmt').hide();
        $('.cmt-box').hide();
        $('.cmt-noti').show();
    } else {
        // $('#user-ava-cmt').show();
        $('.cmt-box').show();
        $('.cmt-noti').hide();
    }
}

function handleAvaAuth() {
    if (isLogin()) {
        const user = JSON.parse(sessionStorage.getItem('user'));
        $('.avatar-temp-cmt').attr('data-name', user.name);
        if (user.avatar) {
            $('.avatar-temp-cmt')
                .css({
                    'background-image': `url(${user.avatar})`,
                    'background-size': 'cover',
                    'background-position': 'center',
                    'background-repeat': 'no-repeat',
                })
                .empty();
        } else {
            $('.avatar-temp-cmt').text(user.name[0]).css({
                'background-color': '#787978',
            });
        }
    }
}

async function handleGetLikedCommentIds() {
    const currentMangaDetail = window.mangaDetail || (typeof mangaDetail !== 'undefined' ? mangaDetail : null);
    if (!currentMangaDetail || !currentMangaDetail.id) {
        return;
    }
    
    const hasComment =
        document.querySelectorAll('.list-comments > .cmt-line').length > 0;
    if (hasComment) {
        const likedCommentIds = await getLikedCommentIds(currentMangaDetail.id);
        document.querySelectorAll('.cm-btn-like').forEach((likeBtn) => {
            const commentId = likeBtn
                .closest('.cmt-line')
                .getAttribute('data-parent-id');
            if (likedCommentIds.includes(+commentId)) {
                likeBtn.querySelector('i').classList.remove('icon-like');
                likeBtn.querySelector('i').classList.add('icon-liked');
            }
        });
    }
}

handleCallbackCheckAuthIsDone(handleCheckLoginCommentInput);
handleCallbackCheckAuthIsDone(() => addModalLogin($('a.btn-reply')));
handleCallbackCheckAuthIsDone(() => addModalLogin($('.ib-like span')));
handleCallbackCheckAuthIsDone(handleAvaAuth);

$(document).ready(function() {
    if ((typeof window.mangaDetail !== 'undefined' || typeof mangaDetail !== 'undefined') && typeof handleGetLikedCommentIds === 'function') {
        handleCallbackCheckAuthIsDone(handleGetLikedCommentIds);
    }
});

$('#content-comments').on('click', '.btn-reply', function (e) {
    e.preventDefault();

    var parentCmtLine = this.closest('.cmt-line');
    if (!parentCmtLine) return;

    var outermostParentCmtLine =
        parentCmtLine.closest('.cmt-line[data-parent-id]') || parentCmtLine;

    var replyBox = outermostParentCmtLine.querySelector('.reply-box');
    if (!replyBox) {
        console.error('Reply box not found');
        return;
    }

    var cmtLineInsideReply = replyBox.querySelector('.cmt-line');

    document.querySelectorAll('.reply-box').forEach(function (element) {
        if (element !== replyBox) {
            var innerCmtLine = element.querySelector('.cmt-line');
            if (innerCmtLine) {
                innerCmtLine.innerHTML = '';
                innerCmtLine.classList.replace('d-flex', 'd-none');
            }
            element.classList.remove('active');
        }
    });
    let userStorage = sessionStorage.getItem('user');
    const user = JSON.parse(userStorage);

    function toggleReplyBox(replyBox, cmtLineInsideReply, replyHTML) {
        if (!isLogin()) {
            return;
        }
        if (!replyBox.classList.contains('active')) {
            if (cmtLineInsideReply) {
                cmtLineInsideReply.innerHTML = replyHTML;
                cmtLineInsideReply
                    .querySelector('.avatar-temp-cmt')
                    .setAttribute('data-name', user?.name || '');
                cmtLineInsideReply.classList.replace('d-none', 'd-flex');
                setTimeout(function () {
                    var inputElement = cmtLineInsideReply.querySelector(
                        '.comment-form textarea',
                    );
                    if (inputElement) {
                        inputElement.focus();
                    }
                }, 0);
            }
            replyBox.classList.add('active');
        } else {
            if (cmtLineInsideReply) {
                cmtLineInsideReply.innerHTML = '';
                cmtLineInsideReply.classList.replace('d-flex', 'd-none');
            }
            replyBox.classList.remove('active');
        }
    }

    if (replyBox.classList.contains('active')) {
        setTimeout(function () {
            var inputElement = cmtLineInsideReply.querySelector(
                '.comment-form textarea',
            );
            if (inputElement) {
                inputElement.focus();
            }
        }, 0);
    } else {
        toggleReplyBox(replyBox, cmtLineInsideReply, replyHTML);
    }
    handleCallbackCheckAuthIsDone(handleAvaAuth);
});

// handle like comment
$('#content-comments').on(
    'click',
    '.cm-btn-like',
    debounce(async function (e) {
        if (!isLogin()) {
            return;
        }

        const likeBtn = e.target.closest('.cm-btn-like');
        const icon = likeBtn.querySelector('i');
        if (!icon) return;

        const commentId = likeBtn
            .closest('.cmt-line')
            .getAttribute('data-parent-id');

        const response = await likeComment(commentId);

        if (response) {
            const isLiked = response.isLiked;
            icon.className = isLiked ? 'icon-liked' : 'icon-like';
            likeBtn.querySelector('span').textContent = response.totalLike;
        }
    }, 200),
);
