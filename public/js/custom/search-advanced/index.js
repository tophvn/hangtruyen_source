const listMangasElem = $('.search-wrapper .col-lg-8');
const filterWrapper = $('.search-wrapper');
const pageElem = $('.search-wrapper .search-result .pagination');
const countPage = $('.search-wrapper .search-result .pagination').attr(
    'data-count-page',
);
const url = new URL(window.location.href);
let currentPage = Number(url.searchParams.get('page')) || 1;

async function handleFillChossenData() {
    const genreElements = filterWrapper.find('.list-genres>span');
    const chosenGenreIds = url.searchParams.get('genreIds') ? .split(',') || [];
    for (let i = 0; i < genreElements.length; i++) {
        const genreId = $(genreElements[i]).attr('data-value');
        if (genreId && chosenGenreIds.includes(genreId)) {
            $(genreElements[i]).addClass('active');
        }
    }

    const chosenCategoryIds =
        url.searchParams.get('categoryIds') ? .split(',') || [];
    const categoryElements = filterWrapper.find('.list-cats .form-check input');
    let allCate = true;
    for (let i = 0; i < categoryElements.length; i++) {
        const cateId = $(categoryElements[i]).attr('value');
        if (cateId && chosenCategoryIds.includes(cateId)) {
            $(categoryElements[i]).prop('checked', true);
        } else if (cateId) {
            allCate = false;
        }
    }
    if (allCate) {
        filterWrapper.find('#cat--all').prop('checked', true);
    }

    const chosenOrder = url.searchParams.get('orderBy');
    if (chosenOrder) {
        const orderElements = filterWrapper.find(
            '#dd-sort .dropdown-menu .dropdown-item',
        );
        for (const orderElement of orderElements) {
            if ($(orderElement).attr('data-value') === chosenOrder) {
                var selectedText = $(orderElement).text();
                var selectedValue = $(orderElement).data('value');
                var dropdown = $(orderElement).closest('.dropdown');
                var dropdownToggle = dropdown.find('.dropdown-toggle');
                var iconElement = dropdownToggle.find('i');
                var subElement = dropdownToggle.find('sub');

                if (subElement.length) {
                    subElement.text(selectedText);
                } else {
                    var newSubElement = $('<sub>').text(selectedText);
                    iconElement.before(newSubElement);
                }

                dropdown.attr('data-value', selectedValue);
            }
        }
    }
}
handleFillChossenData();

async function handleSearchAdvance(isMovePage) {
    const categoryIds = filterWrapper
        .find('.list-cats')
        .find('input:checkbox:checked')
        .map(function() {
            return parseInt($(this).val());
        })
        .get();
    const genreIds = filterWrapper
        .find('.list-genres>span.active')
        .map(function() {
            return parseInt($(this).attr('data-value'));
        })
        .get();

    const orderBy =
        filterWrapper.find('#dd-sort').attr('data-value') || undefined;
    const keyword = filterWrapper.find('input').val();

    window.scrollTo({
        top: 0,
        behavior: 'smooth',
    });

    currentPage = isMovePage ? currentPage : 1;

    url.searchParams.set('page', currentPage);

    if (keyword) {
        url.searchParams.set('keyword', keyword);
    } else {
        url.searchParams.delete('keyword');
    }
    if (categoryIds.length) {
        url.searchParams.set(
            'categoryIds',
            categoryIds.filter((c) => !isNaN(c)).join(','),
        );
    } else {
        url.searchParams.delete('categoryIds');
    }
    if (genreIds.length) {
        url.searchParams.set('genreIds', genreIds.join(','));
    } else {
        url.searchParams.delete('genreIds');
    }
    if (orderBy) {
        url.searchParams.set('orderBy', orderBy);
    } else {
        url.searchParams.delete('orderBy');
    }

    window.location.href = url.href;

    // const res = await searchAdvance(
    //     keyword,
    //     currentPage,
    //     categoryIds,
    //     genreIds,
    //     // countChapter,
    //     orderBy,
    // );

    // if (res) {
    //     listMangasElem.empty().append(res);
    //     observeNewImages();
    // }
}

$('a.btn-filter').on('click', async function(e) {
    e.preventDefault();
    await handleSearchAdvance(false);
});

// Pagination
filterWrapper.on('click', '.pagination > li', async function(e) {
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
    currentPage = isNextPage ?
        currentPage + 1 :
        isPrevPage ?
        currentPage - 1 :
        dataPage;

    url.searchParams.set('page', currentPage);
    window.location.href = url.href;
});

$('.form-search-normal > .form-search').on('submit', function(e) {
    e.preventDefault();
    handleSearchAdvance(false);
});

$('#view-all-tags').on('click', function(e) {
    e.preventDefault();
    $(this).attr('style', 'display: none !important');
    $(this)
        .parent()
        .find('.list-genres > span')
        .each((i, e) => {
            $(e).removeClass('d-none');
        });
});