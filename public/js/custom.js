let hasWebP = !1;
!(function() {
    let A = new Image();
    (A.onload = function() {
        hasWebP = !!(A.height > 0 && A.width > 0);
    }),
    (A.onerror = function() {
        hasWebP = !1;
    }),
    (A.src =
        'data:image/webp;base64,UklGRjoAAABXRUJQVlA4IC4AAACyAgCdASoCAAIALmk0mk0iIiIiIgBoSygABc6WWgAA/veff/0PP8bA//LwYAAA');
})();

function lazyImg(o) {
    return function() {
        function r(e) {
            //   e.onload = function() {
            //     e.classList.remove(o),
            //     e.classList.add("lazyloaded")
            //   },
            //e.dataset.lazybackground && (e.style.backgroundImage = "url(".concat(e.dataset.lazybackground, ")")),
            //e.referrerPolicy = "no-referrer",
            e.getAttribute('data-src') &&
                ((e.src = !hasWebP ?
                        e.dataset.src.replace(/-rw$/, '') :
                        e.dataset.src),
                    'IntersectionObserver' in window && t.unobserve(e));
            // e.classList.remove('preload');

            // e.setAttribute('width', '100%');
            // e.setAttribute('height', '100%');
        }
        var t,
            e = document.querySelectorAll('.' + o);
        if ('IntersectionObserver' in window)
            (t = new IntersectionObserver(
                function(e) {
                    e.forEach(function(e) {
                        0 < e.intersectionRatio && r(e.target);
                    });
                }, {
                    rootMargin: '0px',
                    threshold: 0,
                },
            )),
            e.forEach(function(e) {
                t.observe(e);
            });
        else
            for (var n = 0; n < e.length; n++) r(e[n]);
    };
}
document.addEventListener('DOMContentLoaded', lazyImg('lzl'), {
    passive: true,
});

// Fix click modal search
document.addEventListener('DOMContentLoaded', function() {
    var modalContent = document.querySelector('#searchModal .modal-content');
    if (modalContent) {
        modalContent.addEventListener('click', function(event) {
            event.stopPropagation();
        });
    }
});

// Enter for search form
const form = document.getElementById('form-search');
if (form) {
    form.addEventListener('submit', function(event) {
        event.preventDefault();
        const input = form.querySelector('input');
        const query = input.value;
        const action = form.getAttribute('action');
        window.location.href = action + '?keyword=' + query;
    });
}

//Hover trend manga item

document.addEventListener('DOMContentLoaded', function() {
    var posts = document.querySelectorAll('.m-trend .m-post');

    if (posts) {
        posts.forEach(function(post) {
            var path = post.querySelector('svg path');

            if (path) {
                var strokeColor = path.getAttribute('stroke');
                post.querySelector('.p-thumb').style.setProperty(
                    '--before-bg',
                    strokeColor,
                );
                post.addEventListener('mouseenter', function() {
                    path.setAttribute('fill', strokeColor);
                });

                post.addEventListener('mouseleave', function() {
                    path.setAttribute('fill', 'none');
                });
            }
        });
    }
});

//Show/off Password

//Back to top
var button = document.getElementById('back-to-top');

if (button) {
    // Hide button initially
    button.style.opacity = '0';
    button.style.display = 'flex';
    button.style.transition = 'opacity 0.3s';

    var lastScrollTop = 0;

    // Show/hide button based on scroll direction
    window.addEventListener('scroll', function() {
        var scrollTop =
            window.pageYOffset || document.documentElement.scrollTop;
        if (scrollTop < lastScrollTop && scrollTop > 100) {
            // Scrolling up
            button.style.opacity = '1';
        } else {
            // Scrolling down
            button.style.opacity = '0';
        }
        lastScrollTop = scrollTop <= 0 ? 0 : scrollTop; // For Mobile or negative scrolling
    });

    // Add click event to scroll to top
    button.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

// Light mode
checkDarkModeConfig();
const btnDarkmode = document.querySelectorAll('.dark-mode');
if (btnDarkmode) {
    btnDarkmode.forEach(function(btn) {
        btn.addEventListener('click', function(event) {
            event.preventDefault();
            toggleDarkModeConfig();
        });
    });
}

// Button nav toggle mobile header
const [btnSearchMobile, btnMenuMobile] = document.querySelectorAll(
    '.top-right-mb button',
);
const NavMobile = document.querySelector('.navbar-collapse');
const DropdownMobile = document.querySelectorAll(
    '.navbar-collapse li.dropdown',
);

function toggleNav(event) {
    NavMobile.classList.toggle('open');
    event.preventDefault();
}

function toggleIcon() {
    const icon = btnMenuMobile.querySelector('i');
    icon.classList.toggle('fa-times');
}

function toggleNavAndIcon(event) {
    toggleNav(event);
    toggleIcon();
}

const buttons = document.querySelectorAll('.top-right-mb button');
buttons.forEach(function(button) {
    button.addEventListener('click', toggleNavAndIcon);
});

DropdownMobile.forEach(function(dropdown) {
    dropdown.addEventListener('click', function() {
        this.classList.toggle('open');
    });
});

//Check spoil comment
const btnSpoil = document.querySelector('.btn-spoil');

function btnCheckSpoil(event) {
    if (!btnSpoil.contains(event.target)) {
        return;
    }

    btnSpoil.classList.toggle('checked');
    const ionIconSpoil = btnSpoil.querySelector('ion-icon');
    const iconName = ionIconSpoil.getAttribute('name');
    if (iconName === 'square-outline') {
        ionIconSpoil.setAttribute('name', 'checkmark');
    } else {
        ionIconSpoil.setAttribute('name', 'square-outline');
    }
    event.preventDefault();
}
if (btnSpoil) {
    this.addEventListener('click', btnCheckSpoil);
}

//Close group button Comment
const btnCloseCmts = document.querySelectorAll('.btn-close-cmt');

function handleCloseCmt(event) {
    const ReplyBoxCmt = this.closest('.is-reply');
    if (ReplyBoxCmt) {
        ReplyBoxCmt.style.display = 'none';
    }
    const ciButtons = this.closest('.ci-buttons');
    if (ciButtons) {
        ciButtons.style.display = 'none';
    }
    event.preventDefault();
}

btnCloseCmts.forEach((btnCloseCmt) => {
    btnCloseCmt.addEventListener('click', handleCloseCmt);
});

//Spoil comments
const btnSpamComments = document.querySelectorAll('.btn-spam');
btnSpamComments.forEach((button) => {
    button.addEventListener('click', function() {
        var paragraph = button.closest('.cmt-line').querySelector('.ibody p');
        paragraph.classList.toggle('spoiler-visible');

        var icon = button.querySelector('i');
        if (icon.classList.contains('icon-eye')) {
            icon.classList.remove('icon-eye');
            button.classList.add('icon-eye-slash');
            button.setAttribute('title', 'Ẩn đi');
        } else {
            icon.classList.add('icon-eye');
            button.classList.remove('icon-eye-slash');
            icon.setAttribute('title', 'Hiển thị');
        }
    });
});

//Show comment form

var commentInput = document.querySelector('.comment-input');
var userNameElement = commentInput ? commentInput.querySelector('.user-name') : null;
var replyHTML = commentInput ? commentInput.innerHTML : '';

if (userNameElement) {
    replyHTML = replyHTML.replace(userNameElement.outerHTML, '');
}

var notiTimeout = null;

function alertNoti(content) {
    clearTimeout(notiTimeout);
    let noti = document.getElementById('vote_noti');
    $(noti).find('p').text(content);
    noti.style.bottom = '32px';
    notiTimeout = setTimeout(function() {
        noti.style.bottom = '-100px';
    }, 5000);
}

//Tag genres
const genresHashTag = document.querySelectorAll('.list-genres > span');
genresHashTag.forEach((genreTag) => {
    genreTag.addEventListener('click', function() {
        genreTag.classList.toggle('active');
    });
});

// Function to reapply lazy loading to newly added images
var observeNewImages = function() {
    // var newLazyImages = document.querySelectorAll('.lzl:not([data-observed])');
    // newLazyImages.forEach(function (img) {
    //     img.setAttribute('data-observed', true); // Mark image as observed to avoid duplication
    // });
    lazyImg('lzl')();
};

document.addEventListener('DOMContentLoaded', function() {
    var offcanvasElements = document.querySelectorAll('.offcanvas');

    offcanvasElements.forEach(function(offcanvasElement) {
        offcanvasElement.addEventListener('shown.bs.offcanvas', function() {
            // Thêm lớp open-offcanvas vào body khi offcanvas được hiển thị
            document.body.classList.add('open-offcanvas');
        });

        offcanvasElement.addEventListener('hidden.bs.offcanvas', function() {
            // Loại bỏ lớp open-offcanvas khỏi body khi offcanvas bị ẩn
            document.body.classList.remove('open-offcanvas');
        });
    });
});

$(document).ready(function() {
    $('.dropdown .dropdown-item').on('click', function(e) {
        e.preventDefault();

        var selectedText = $(this).text();
        var selectedValue = $(this).data('value');
        var dropdown = $(this).closest('.dropdown');
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
    });
});

// handle checkbox all
$('input.checkbox-all').on('change', function() {
    const isChecked = $(this).is(':checked');
    $(this)
        .closest('.list-checkbox')
        .find('input[type="checkbox"]')
        .prop('checked', isChecked);
});

// View all tags
function viewAllTags(e) {
    if (e) {
        e.preventDefault();
    }
    const viewAllBtns = document.querySelectorAll('.dropdown-menu .view-all');
    const moreSections = document.querySelectorAll('.more-tags');

    viewAllBtns.forEach((viewAllBtn, index) => {
        if (viewAllBtn && moreSections[index]) {
            moreSections[index].style.display = 'block';
            viewAllBtn.style.display = 'none';
        }
    });
}