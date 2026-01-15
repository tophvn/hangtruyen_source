const getCookie = (key) => {
    let cookie = {};
    document.cookie.split(';').forEach(function(el) {
        let [key, value] = el.split('=');
        cookie[key.trim()] = value;
    });
    if (typeof cookie[key] !== 'undefined') {
        return cookie[key];
    }
    return null;
};