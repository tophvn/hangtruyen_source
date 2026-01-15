const AUTHEN_DONE_EVENT = 'customAuthen';

const LIST_COLOR_AVA = [
    '#c43939',
    '#d0a71a',
    '#83d01a',
    '#2ce941',
    '#0ec067',
    '#10dfcc',
    '#1055df',
    '#6320e8',
    '#bb20e8',
    '#e820d0',
    '#e82081',
    '#e82020',
];

const getAvaColor = (name) => {
    const index = name.charCodeAt(0) % LIST_COLOR_AVA.length;
    return LIST_COLOR_AVA[index];
};

(async function checkAuthen() {
    handleRemoveUserFromSessionStorage();
    handleClearWindowHref();

    let user = await getUser();
    if (!user) {
        user = await refreshToken();
    }

    if (user) {
        // handle header
        handleSaveUserToSessionStorage(user);
    } else {
        handleRemoveUserFromSessionStorage();
    }

    $(document).ready(function() {
        setTimeout(() => {
            $('#auth').trigger(AUTHEN_DONE_EVENT);
        }, 0);
    });
})();

function handleClearWindowHref() {
    var url = document.location.href;
    const newUrl = new URL(url);
    if (newUrl.searchParams.has('at')) {
        newUrl.searchParams.delete('at');
        window.history.pushState({}, '', newUrl);
    }
}

function handleCallbackCheckAuthIsDone(callBack) {
    const user = sessionStorage.getItem('user');

    if (!user) {
        $(document).ready(function() {
            $('#auth').on(AUTHEN_DONE_EVENT, function() {
                callBack && typeof callBack === 'function' && callBack();
            });
        });
    } else {
        callBack();
    }
}

function debounce(func, wait, immediate) {
    var timeout;
    return function() {
        var context = this,
            args = arguments;
        var later = function() {
            timeout = null;
            if (!immediate) func.apply(context, args);
        };
        var callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func.apply(context, args);
    };
}

function isLogin() {
    const user = sessionStorage.getItem('user');
    return !!user;
}

function addActionModalLogin(elem) {
    $(elem)
        .attr('data-bs-toggle', 'modal')
        .attr('data-bs-target', '#loginModal');
}

function addModalLogin(...buttons) {
    if (!isLogin()) {
        for (const button of buttons) {
            addActionModalLogin(button);
        }
    }
}

function handleSetReadChapter(chapterId) {
    const user = getUserFromSessionStorage() || {
        id: 'guest'
    };
    const readChapterKey = `${user.id}-read-chapter`;
    const chapters = localStorage.getItem(readChapterKey) ?
        JSON.parse(localStorage.getItem(readChapterKey)) :
        [];

    if (!chapters.includes(chapterId)) {
        chapters.push(chapterId);
        localStorage.setItem(readChapterKey, JSON.stringify(chapters));
    }
}