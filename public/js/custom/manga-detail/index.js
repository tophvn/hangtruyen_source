$(document).ready(function() {
    function checkAndInit() {
        if (typeof window.mangaDetail === 'undefined' || !window.mangaDetail) {
            setTimeout(checkAndInit, 500);
            return;
        }
        
        initializeMangaDetailScript();
    }
    
    setTimeout(checkAndInit, 100);
});

function initializeMangaDetailScript() {
    const followButton = $('.manga-save');
    const voteButton = $('.vote-rate .options a');
    const listChapters = $('.list-chapters');
    const elemListChapters = listChapters.find('.l-chapter');

    handleCallbackCheckAuthIsDone(() => addModalLogin(followButton, voteButton));

    function handleCheckFollowAndVote() {
        if (!isLogin()) {
            return;
        }
        
        const currentMangaDetail = window.mangaDetail || (typeof mangaDetail !== 'undefined' ? mangaDetail : null);
        if (!currentMangaDetail || !currentMangaDetail.id) {
            return;
        }
        
        if (typeof getUserFromSessionStorage !== 'function') {
            return;
        }
        
        const user = getUserFromSessionStorage();
        if (currentMangaDetail && currentMangaDetail.isFollowing) {
            $(followButton).addClass('active');
        }

        const currentUserVote = window.userVote || (typeof userVote !== 'undefined' ? userVote : null);
        if (currentUserVote !== null && currentUserVote !== undefined) {
            $(`.manga-vote-btn`).addClass('un-select');
            $(`.manga-vote-btn[data-vote="${currentUserVote}"]`).removeClass('un-select');
        }
    }

    if ((typeof window.mangaDetail !== 'undefined' && window.mangaDetail) && typeof handleCheckFollowAndVote === 'function') {
        handleCallbackCheckAuthIsDone(handleCheckFollowAndVote);
    }

    $('.list-chapters-wrapper .form-search input').on(
        'keyup',
        debounce(function () {
            const keyword = $(this).val();
            if (keyword) {
                listChapters.addClass('reserve-list');
                for (const elemChapter of elemListChapters) {
                    const text = $(elemChapter)
                        .find('a')
                        .attr('title')
                        .toLowerCase();
                    if (text.includes(keyword.toLowerCase())) {
                        $(elemChapter).removeClass('d-none');
                    } else {
                        $(elemChapter).addClass('d-none');
                    }
                }
            } else {
                listChapters.removeClass('reserve-list');
                elemListChapters.removeClass('d-none');
            }

            listChapters.scrollTop(0);
        }, 200),
    );

    followButton.on('click', async function (e) {
        e.preventDefault();
        if (!isLogin()) {
            return;
        }
        
        const currentMangaDetail = window.mangaDetail || (typeof mangaDetail !== 'undefined' ? mangaDetail : null);
        if (!currentMangaDetail || !currentMangaDetail.id) {
            return;
        }

        const response = await followManga(currentMangaDetail.id);
        if (response) {
            if (response.isFollowing) {
                $(this).addClass('active');
            } else {
                $(this).removeClass('active');
            }
            
            if (response.followsCount !== undefined) {
                const followsCount = response.followsCount;
                const followsCountText = followsCount >= 1000000 
                    ? (followsCount / 1000000).toFixed(1) + 'M'
                    : (followsCount >= 1000 
                        ? (followsCount / 1000).toFixed(1) + 'K'
                        : followsCount.toString());
                
                $(this)
                    .next('.num-follow')
                    .text(followsCountText + ' lượt theo dõi');
                
                if (currentMangaDetail) {
                    currentMangaDetail.sourceFollow = followsCount;
                }
            }
        }
    });

    $(document).on('click', '.vote-rate .options a.manga-vote-btn', async function (e) {
        e.preventDefault();
        e.stopPropagation();
        
        if ($(this).hasClass('login-required')) {
            return false;
        }
        
        if (!isLogin()) {
            return false;
        }
        
        const currentMangaDetail = window.mangaDetail || (typeof mangaDetail !== 'undefined' ? mangaDetail : null);
        
        if (!currentMangaDetail || !currentMangaDetail.id) {
            return false;
        }
        
        const selectedVoteElem = this;
        const voteData = parseInt($(this).attr('data-vote'));
        
        if (!voteData || voteData < 1 || voteData > 5) {
            return false;
        }
        
        const response = await voteManga(currentMangaDetail.id, voteData);
        
        if (response) {
            if (typeof window !== 'undefined') {
                window.userVote = voteData;
            }
            
            $(this)
                .closest('.options')
                .find('a.manga-vote-btn')
                .addClass('un-select');
            
            $(this).removeClass('un-select');
            
            if (typeof alertNoti === 'function') {
                alertNoti('Cảm ơn bạn đã nhận xét truyện');
            } else {
                alert('Cảm ơn bạn đã nhận xét truyện');
            }
        }
        
        return false;
    });

    let isReversedOrderChapters = false;
    $('.sort-chapter i').on('click', function () {
        isReversedOrderChapters = !isReversedOrderChapters;
        if (isReversedOrderChapters) {
            elemListChapters.parent().append(elemListChapters.get().reverse());
        } else {
            elemListChapters.parent().append(elemListChapters.get());
        }
    });

    async function handleGetCurrentReadingChapter() {
        return null;
    }

    const url = new URL(window.location.href);
    if (url.hash) {
        const highlightComment = document.querySelector(url.hash);
        if (highlightComment) {
            highlightComment.classList.add('mask');
        }
    }
}
