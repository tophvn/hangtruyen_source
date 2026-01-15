const followButton = $('.manga-save');
const voteButton = $('.vote-rate .options a');
const listChapters = $('.list-chapters');
const elemListChapters = listChapters.find('.l-chapter');

handleCallbackCheckAuthIsDone(() => addModalLogin(followButton, voteButton));

function handleCheckFollowAndVote() {
    if (!isLogin) {
        return;
    }
    const user = getUserFromSessionStorage();

    if (user?.following?.includes(mangaDetail.id)) {
        $(followButton).addClass('active');
    }

    $(`.manga-vote-btn`).addClass('un-select');
    if (user?.votes[mangaDetail.id]) {
        $(
            `.manga-vote-btn[data-vote="${user.votes[mangaDetail.id]}"]`,
        ).removeClass('un-select');
    } else {
        $(`.manga-vote-btn`).removeClass('un-select');
    }
}

handleCallbackCheckAuthIsDone(handleCheckFollowAndVote);

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

        // Scroll to top of list after filtering
        listChapters.scrollTop(0);
    }, 200),
);

followButton.on('click', async function (e) {
    e.preventDefault();
    if (!isLogin()) {
        return;
    }

    const response = await followManga(mangaDetail.id);
    if (response) {
        if (response.isFollowing) {
            $(this).addClass('active');
            ++mangaDetail.sourceFollow;
        } else {
            $(this).removeClass('active');
            --mangaDetail.sourceFollow;
        }
        $(this)
            .next()
            .text(
                mangaDetail.sourceFollow
                    .toString()
                    .replace(/\B(?=(\d{3})+(?!\d))/g, '.') + ' lượt theo dõi',
            );
    }
});

$('.vote-rate .options a').on('click', async function (e) {
    e.preventDefault();
    if (!isLogin()) {
        return;
    }
    const selectedVoteElem = this;
    const voteData = parseInt($(this).attr('data-vote'));
    if (voteData !== userVote) {
        const response = await voteManga(mangaDetail.id, voteData);
        if (response) {
            userVote = voteData;
            $(this)
                .closest('.options')
                .find('a')
                .each(function (_, VoteElem) {
                    if (selectedVoteElem !== VoteElem) {
                        $(this).addClass('un-select');
                    } else {
                        $(this).removeClass('un-select');
                    }

                    alertNoti('Cảm ơn bạn đã nhận xét truyện');
                });
        }
    }
});

// action sort list chapter
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
    const user = getUserFromSessionStorage();

    if (!user) {
        return null;
    }
    const data = await $.ajax({
        type: 'GET',
        xhrFields: { withCredentials: true },
        url: `${apiUrl}/users/mg-cr-rd?mangaId=${mangaDetail.id}&userId=${user.id}`,
        contentType: 'application/json',
    })
        .catch((err) => {
            // console.log('set user current reading error', err);
            return null;
        })
        .then((res) => {
            return res;
        });

    if (!data) {
        return;
    }

    const chapter = mangaDetail.chapters.find(
        (chapter) => chapter.id === data.chapterId,
    );

    if (!chapter) {
        return;
    }

    const nextChapterBtn = $('#btn-read_next');
    $(nextChapterBtn).removeAttr('hidden');
    $(nextChapterBtn).attr(
        'href',
        `/truyen-tranh/${mangaDetail.rawSlug}/${chapter.slug}`,
    );
}

handleCallbackCheckAuthIsDone(handleGetCurrentReadingChapter);

const url = new URL(window.location.href);
if (url.hash) {
    const highlightComment = document.querySelector(url.hash);
    if (highlightComment) {
        highlightComment.classList.add('mask');
    }
}
