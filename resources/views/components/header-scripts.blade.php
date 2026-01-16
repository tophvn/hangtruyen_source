<script>
    if (typeof listMangaElem === 'undefined') {
        var listMangaElem = $('#form-search .result').first();
    }

    function renderMatchedTitle(search, title) {
        const collator = new Intl.Collator(undefined, {
            sensitivity: 'base'
        });
        const searchWords = search.toLowerCase().split(/\s+/);
        const titleLower = title.toLowerCase();

        const fullMatchIndex = titleLower.indexOf(search.toLowerCase());
        if (fullMatchIndex !== -1) {
            const matched = title.slice(
                fullMatchIndex,
                fullMatchIndex + search.length,
            );
            return (
                title.slice(0, fullMatchIndex) +
                `<span class="color">${matched}</span>` +
                title.slice(fullMatchIndex + search.length)
            );
        }

        const matches = searchWords.map((word) => {
            let index = -1;
            for (let i = 0; i <= titleLower.length - word.length; i++) {
                if (
                    collator.compare(
                        titleLower.slice(i, i + word.length),
                        word,
                    ) === 0
                ) {
                    index = i;
                    break;
                }
            }

            if (index === -1) {
                return {
                    index: -1,
                    matched: null
                };
            }

            const matched = title.slice(index, index + word.length);
            return {
                index,
                matched
            };
        });

        let result = title;
        for (const matched of matches) {
            if (matched.matched) {
                result = result.replace(
                    matched.matched,
                    `<span class="color">${matched.matched}</span>`,
                );
            }
        }
        return result;
    }

    function appendRecommendMangas(mangas, keyword) {
        listMangaElem.empty();
        listMangaElem.append(
            mangas.map((manga) => {
                const title = manga.title;
                const posterPath = manga.posterPath;
                const slug = manga.slug;
                return `<li>
                    <div class="p-thumb flex-shrink-0">
                        <a title="${title}" href="${slug}" rel="nofollow">
                            <img
                                class="img-poster"
                                data-original="${posterPath}"
                                alt="${title}"
                                src="${posterPath}"
                            />
                        </a>
                    </div>
                    <div class="p-content flex-grow-1">
                        <h3 class="m-name">
                            <a href="${slug}">${renderMatchedTitle(keyword, manga.title)}</a>
                        </h3>
                        <div class="group-star">
                            <div class="list-chaps">
                                ${manga.chapters.slice(0, 1).map(chapter => (
                                    `<span class="chapter">
                                        <a data-id="${chapter.id}" href="${manga.slug}/${chapter.slug}" title="${chapter.name}">
                                            ${chapter.name}
                                        </a>
                                    </span>`
                                ))}
                            </div>
                        </div>
                    </div>
                </li>`;
            }),
        );
    }

    $('#form-search input').on(
        'keyup',
        debounce(function() {
            const keyword = $(this).val();
            if (keyword) {
                $('#search-suggest .tab-content > a').attr(
                    'href',
                    `/tim-kiem?keyword=${keyword}`,
                );
            }
        }, 200),
    );

    $(document).ready(function() {
        const forms = $('.form-search');
        const btnToggleSearchForms = $('.toggle-formsearch');

        if (btnToggleSearchForms.length) {
            btnToggleSearchForms.each(function(index) {
                $(this).on('click', function() {
                    const form = forms.eq(index);
                    form.toggleClass('active-mobile');
                    form.find('input').focus();
                    form.find('.search-result-wrapper').show();
                });
            });
        }

        forms.find('input').on('focus', function() {
            $(this)
                .closest('.form-search')
                .find('.search-result-wrapper')
                .show();
        });

        $(document).on('click', function(event) {
            if (!$(event.target).closest(
                    '.form-search, a.toggle-formsearch, .search-result-wrapper',
                ).length) {
                $('.search-result-wrapper').hide();
                forms.removeClass('active-mobile');
            }
        });
        $('.form-search .overlay').on('click', function() {
            $(this)
                .closest('.form-search')
                .find('.search-result-wrapper')
                .hide();
            $(this).closest('.form-search').removeClass('active-mobile');
        });
    });

    function handleHeaderGetUserInfoFromLS() {
        if (typeof getUserFromSessionStorage !== 'function') {
            return;
        }
        let user = getUserFromSessionStorage();
        if (user) {
            handleHeaderLoginSuccess(user);
        } else {
            handleHeaderLogout();
        }
    }

    function handleHeaderLoginSuccess(user) {
        $('#username').text(user.name);
        
        if (user.avatar) {
            $('#avatar-temp-header').css({
                'background-image': `url(${user.avatar})`,
                'background-size': 'cover',
                'background-position': 'center',
                'background-repeat': 'no-repeat',
            }).text('').attr('data-name', '');
        } else {
            $('#avatar-temp-header').attr('data-name', user.name);
            const firstLetter = user.name ? user.name[0].toUpperCase() : '?';
            const avaColor = (typeof getAvaColor !== 'undefined' && getAvaColor) 
                ? getAvaColor(user.name || 'User') 
                : '#787978';
            $('#avatar-temp-header').text(firstLetter).css({
                'background-image': '',
                'background-color': avaColor,
            });
        }

        if (user.unreadNoti) {
            $('#box-noti .badge-noti').text(user.unreadNoti).removeAttr('hidden');
        } else {
            $('#box-noti .badge-noti').attr('hidden', true);
        }

        $('#not-loggin').attr('hidden', true);
        $('#has-login').removeAttr('hidden');
        $('#box-noti').removeAttr('hidden');
    }

    function handleHeaderLogout() {
        $('#not-loggin').removeAttr('hidden');
        $('#has-login').attr('hidden', true);
        $('#box-noti').attr('hidden', true);
        $('#userava').attr('src', '');
        $('#username').text('');
        $('#avatar-temp-header').css({
            'background-image': '',
            'background-color': '',
        }).text('').attr('data-name', '');
        $('#box-noti .badge-noti').attr('hidden', true);
    }

    async function fetchListNoti() {
        const response = await $.ajax({
            type: 'GET',
            xhrFields: {
                withCredentials: true
            },
            url: apiUrl + '/notifications',
            contentType: 'application/json',
        }).catch(() => {
            return null;
        });

        if (response && response.status === 200) {
            return response.data;
        }

        return null;
    }
    
    $(document).on('click', '#logout', async function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        if (typeof handleRemoveUserFromSessionStorage === 'function') {
            handleRemoveUserFromSessionStorage();
        }
        
        if (typeof handleHeaderLogout === 'function') {
            handleHeaderLogout();
        }
        
        try {
            await $.ajax({
                type: 'POST',
                url: '/api/auth/logout',
                contentType: 'application/json',
            });
        } catch (error) {
            console.log('Logout API error:', error);
        }
        
        window.location.href = '/auth/logout';
    });

    var recommendMangas = [];
    async function getRecommendMangas() {
        return null;
    }
    

    // Handle dropdown item clicks - but allow account links to navigate normally
    $(document).on('click', '.dropdown-item', function(e) {
        const $item = $(this);
        const href = $item.attr('href');
        
        // Skip logout button
        if ($item.attr('id') === 'logout') {
            return;
        }
        
        // Allow normal navigation for account links - don't prevent default, don't stop propagation
        if ($item.hasClass('account-link') || (href && (href.includes('/tai-khoan') || href.includes('account')))) {
            // Let browser handle navigation normally - don't interfere at all
            return true;
        }
        
        // For other links, handle programmatically if needed
        if (href && href !== '#' && href !== 'javascript:void(0)') {
            e.preventDefault();
            window.location.href = href;
        }
    });
    
    // Explicitly handle account links with higher priority to ensure navigation works
    $(document).on('click', '#has-login .account-link', function(e) {
        const href = $(this).attr('href');
        if (href) {
            // Close dropdown
            const dropdownElement = document.querySelector('#menuAccount');
            if (dropdownElement) {
                const dropdown = bootstrap.Dropdown.getInstance(dropdownElement);
                if (dropdown) {
                    dropdown.hide();
                }
            }
            // Navigate
            window.location.href = href;
        }
    });
    
    if (typeof checkDarkModeConfig === 'function') {
        checkDarkModeConfig();
    }
    
    async function checkAndUpdateHeader() {
        try {
            const user = await getUser();
            if (user) {
                if (typeof handleSaveUserToSessionStorage === 'function') {
                    handleSaveUserToSessionStorage(user);
                }
                if (typeof handleHeaderLoginSuccess === 'function') {
                    handleHeaderLoginSuccess(user);
                }
            } else {
                if (typeof handleHeaderLogout === 'function') {
                    handleHeaderLogout();
                }
            }
        } catch (error) {
            if (typeof handleHeaderLogout === 'function') {
                handleHeaderLogout();
            }
        }
    }
    
    $(document).ready(function() {
        checkAndUpdateHeader();
        setTimeout(checkAndUpdateHeader, 500);
    });
    
    handleCallbackCheckAuthIsDone(handleHeaderGetUserInfoFromLS);

    $('.btn-noti').on('click', async function() {
        if ($('.noti').hasClass('active')) {
            $('.noti-content').hide();
            $('.noti').removeClass('active');
        } else {
            $('.noti-content').show();
            $('.noti').addClass('active');
            const listNoti = await fetchListNoti();

            $('.noti-content').empty();
            if (listNoti && listNoti.length) {
                $('.noti-content').removeClass('noti-empty');
                listNoti.forEach((noti) => {
                    const notiItem = $(`
                        <div class="noti-item d-flex ${noti.isRead ? 'masked' : ''}">
                            <a class="noti-link" href="${noti.manga.slug}" ></a>
                            <div class="p-thumb flex-shrink-0">
                                <img class="img-poster" data-original="${noti.manga.posterPath}" alt="${noti.manga.title}" src="${noti.manga.posterPath}">
                            </div>
                            <div class="p-content flex-grow-1">
                                <span class="noti-title">${noti.content}</span>
                                <span class="noti-time">${noti.createdAt}</span>
                            </div>
                        </div>
                    `);
                    $('.noti-content').append(notiItem);
                });
            } else {
                $('.noti-content').addClass('noti-empty').append(`
                    <div class="d-flex justify-content-center align-items-center">
                        <span class="noti-title">Không có thông báo nào</span>
                    </div>
                `);
            }
        }
    });
    
    $('.overlay-noti').on('click', function() {
        $('.noti-content').hide();
        $('.noti').removeClass('active');
    });
</script>
