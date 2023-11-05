
export const createCookie = (e: string, t: string, n: number) => {
    if (n) {
        const o = new Date;
        o.setTime(o.getTime() + (n * 24 * 3600 * 1e3));
        var r = "; expires=" + o.toGMTString();
    } else
        var r = "";
    document.cookie = e + "=" + t + r + "; path=/"
}
export const readCookie = (e: string) => {
    for (var t = e + "=", n = document.cookie.split(";"), o = 0; o < n.length; o++) {
        for (var r = n[o];
            " " == r.charAt(0);) r = r.substring(1, r.length);
        if (0 == r.indexOf(t)) return r.substring(t.length, r.length)
    }
    return null
}
export const eraseCookie = (e: string) => {
    createCookie(e, "", -1)
}

