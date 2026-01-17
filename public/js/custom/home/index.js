const followButtons = $('a.manga-save');
const listMangasNewlyUpdated = $('#manga-new_update .splide__track');
const listMangasRecommend = $('#m-suggest .splide__track');
let mangasNewlyUpdated = listMangasNewlyUpdated.children().clone();
let mangasNewlyUpdatedHot = null;

const randomMany = (arr, count) => {
    const shuffled = arr.slice();
    for (let i = shuffled.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]];
    }
    return shuffled.slice(0, count);
};

const handleCheckMangaFollowed = () => {
    const user = getUserFromSessionStorage();
    if (user && user.following && user.following.length) {
        followButtons.each((index) => {
            const mangaId = $(followButtons[index]).data('mangaId');
            if (user.following.includes(mangaId)) {
                $(followButtons[index]).addClass('active');
            }
        });
    }
};

handleCallbackCheckAuthIsDone(() => addModalLogin(followButtons));
handleCallbackCheckAuthIsDone(handleCheckMangaFollowed);

$('.newly-updated-sort .dropdown-menu a').on('click', async function() {
    const dataValue = $(this).data('value');
    if (dataValue === 'mg-hot') {
        if (!mangasNewlyUpdatedHot) {
            mangasNewlyUpdatedHot = await getNewlyUpdatedHot();
        }

        listMangasNewlyUpdated.empty().append(mangasNewlyUpdatedHot);
    } else if (dataValue === 'mg-new') {
        listMangasNewlyUpdated.empty().append(mangasNewlyUpdated);
    }

    observeNewImages();
});

followButtons.on('click', async function(e) {
    e.preventDefault();
    e.stopPropagation();
    if (!isLogin()) {
        return;
    }

    const mangaId = $(this).data('mangaId');
    const response = await followManga(mangaId);
    if (response) {
        if (response.isFollowing) {
            $(document)
                .find(`a.manga-save[data-manga-id="${mangaId}"]`)
                .addClass('active');
            alertNoti('Theo dõi truyện thành công');
        } else {
            $(document)
                .find(`a.manga-save[data-manga-id="${mangaId}"]`)
                .removeClass('active');
            alertNoti('Bỏ theo dõi truyện thành công');
        }
    }
});

function handleCutRandomSuggestManga() {
    const videos = listMangasRecommend
        .find('.splide')
        .map((index, element) => element);

    if (videos.length <= 16) {
        return;
    }

    const videosDelete = randomMany(videos, videos.length - 16);

    for (const video of videosDelete) {
        $(video).remove();
    }
}
handleCutRandomSuggestManga();

//Splide
document.addEventListener('DOMContentLoaded', function() {
    const splideInstances = [
        new Splide('.slide-single', {
            perPage: 1,
            type: 'fade',
            rewind: true,
            lazyLoad: 'nearby',
            autoplay: true,
            interval: 5000,
            speed: 3000,
            pauseOnHover: true,
            pauseOnFocus: true,
            pauseOnLeave: true,
            pauseOnScroll: true,
            pagination: false,
        }).mount(),

        new Splide('.list-manga', {
            grid: {
                rows: 3,
                cols: 2,
                gap: {
                    row: '20px',
                    col: '20px',
                },
            },
            breakpoints: {
                767: {
                    grid: {
                        rows: 3,
                        cols: 1,
                        gap: {
                            col: '10px',
                        },
                    },
                    padding: {
                        right: '10rem'
                    },
                },
                576: {
                    padding: {
                        right: '5rem'
                    },
                },
                480: {
                    padding: {
                        right: '0'
                    },
                },
            },
            type: 'loop',
            pagination: false,
            perMove: 1,
        }).mount(window.splide.Extensions),

        new Splide('.top-genres', {
            perPage: 4,
            pagination: false,
            arrows: false,
            perMove: 1,
            gap: '20px',
            breakpoints: {
                350: {
                    perPage: 1,
                    padding: {
                        right: '5.5rem'
                    },
                },
                480: {
                    perPage: 2,
                    padding: {
                        right: '0'
                    },
                    gap: '10px',
                },
                576: {
                    perPage: 2,
                    padding: {
                        right: '8rem'
                    },
                },
                640: {
                    perPage: 3,
                    padding: {
                        right: '0'
                    },
                },
                991: {
                    perPage: 2,
                    padding: {
                        right: '3rem'
                    },
                },
                1200: {
                    perPage: 3,
                },
            },
        }).mount(),

        new Splide('.m-trend', {
            perPage: 3,
            perMove: 1,
            type: 'loop',
            pagination: false,
            padding: {
                right: '3rem'
            },
            breakpoints: {
                320: {
                    perPage: 1,
                    padding: {
                        right: '0'
                    },
                },
                576: {
                    perPage: 1,
                    padding: {
                        right: '2rem'
                    },
                },
                640: {
                    perPage: 1,
                    padding: {
                        right: '5rem'
                    },
                },
                768: {
                    padding: {
                        right: '0'
                    },
                },
                1200: {
                    perPage: 2,
                    padding: {
                        right: '8rem'
                    },
                },
            },
        }).mount(),

        new Splide('.top-comments', {
            perPage: 3,
            perMove: 1,
            padding: {
                right: '6rem'
            },
            type: 'loop',
            pagination: false,
            autoplay: true,
            interval: 6000,
            speed: 1000,
            pauseOnHover: true,
            pauseOnFocus: true,
            pauseOnLeave: true,
            pauseOnScroll: true,
            arrows: false,
            gap: '16px',
            breakpoints: {
                320: {
                    perPage: 1,
                    padding: {
                        right: '1rem'
                    },
                },
                576: {
                    perPage: 1,
                    padding: {
                        right: '3rem'
                    },
                    gap: '8px',
                },
                768: {
                    perPage: 2,
                },
            },
        }).mount(),

        new Splide('.blog-home', {
            perPage: 5,
            perMove: 1,
            gap: '16px',
            type: 'slide',
            loop: false,
            rewind: false,
            pagination: false,
            padding: {
                right: '3rem'
            },
            breakpoints: {
                1300: {
                    perPage: 4,
                },
                1200: {
                    perPage: 3,
                },
                768: {
                    perPage: 2,
                },
                640: {
                    perPage: 1,
                    gap: '10px',
                    padding: {
                        right: '7rem'
                    },
                },
                576: {
                    perPage: 1,
                    padding: {
                        right: '2rem'
                    },
                },
            },
        }).mount(),
    ];

    splideInstances.forEach((splideInstance) => {
        splideInstance.root.querySelectorAll('[aria-hidden]').forEach((el) => {
            el.removeAttribute('aria-hidden');
        });
    });
});

async function handleUpdateSuggestion() {
    if (!isLogin()) {
        return;
    }
    const htmlListMangas = await getSuggestionMangas();
    listMangasRecommend.empty().append(htmlListMangas);

    handleCutRandomSuggestManga();
    renderSuggest($('#m-suggest .m-suggest').get(0));
    observeNewImages();
}

handleCallbackCheckAuthIsDone(handleUpdateSuggestion);