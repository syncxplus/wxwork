function setCookie(name, value, days) {
    days = (value === null) ? 0 : (days || 30);
    var date = new Date();
    var time = !!days ? (date.getTime() + days * 24 * 60 * 60 * 1000) : (date.getTime() - 1);
    date.setTime(time);
    document.cookie = name + "=" + encodeURIComponent(value) + ";expires=" + date.toUTCString() + ";path=/";
}

function getCookie(name) {
    var data = document.cookie.match(new RegExp("(^| )" + name + "=([^;]*)(;|$)"));
    return !!data ? decodeURIComponent(data[2]) : '';
}

function deleteCookie(name) {
    setCookie(name, null);
}

function fileInfo(file) {
    return file ? (file.files ? file.files[0] : {}) : {}
}

function todo() {
    alert("TODO");
}

function isJson(obj) {
    return typeof(obj) == "object"
        && Object.prototype.toString.call(obj).toLowerCase() == "[object object]"
        && !obj.length;
}

function wxAlert(data) {
    alert(isJson(data) ? JSON.stringify(data) : data);
}
