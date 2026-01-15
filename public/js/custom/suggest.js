const sliders = document.querySelectorAll('.m-suggest');

const renderSuggest = (slider) => {
    new Splide(slider, {
        perPage: 8,
        perMove: 1,
        type: 'loop',
        pagination: false,
        padding: {
            right: '2.5rem'
        },
        breakpoints: {
            480: {
                perPage: 3,
                padding: {
                    right: '0'
                },
            },
            767: {
                perPage: 4,
            },
            992: {
                perPage: 5,
            },
            1200: {
                perPage: 6,
            },
        },
    }).mount();
};

// Khởi tạo Splide cho từng slider
sliders.forEach(function(slider) {
    renderSuggest(slider);
});