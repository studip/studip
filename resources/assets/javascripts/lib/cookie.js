/*jslint esversion: 6*/
class Cookie {
    static set(name, value, expiry_days) {
        var chunks = [name + '=' + value, 'SameSite=strict'];
        if (expiry_days !== undefined) {
            let date = new Date();
            date.setTime(date.getTime() + expiry_days * 24 * 60 * 60 * 1000);

            chunks.push(`expires=${date.toUTCString()}`);
        }
        chunks.push(
            'path=/' + STUDIP.URLHelper.getURL('a', {}, true)
                    .slice(0, -1)
                    .split('/')
                    .slice(3)
                    .map(encodeURIComponent)
                    .join('/')
        );

        document.cookie = chunks.join(';');
    }

    static get(name) {
        let chunks = document.cookie.split(';');
        var data = {};
        chunks.forEach(chunk => {
            let chunks = chunk.split('=');
            data[chunks[0].trim()] = chunks.slice(1).join('=');
        });

        return data.hasOwnProperty(name) ? data[name] : undefined;
    }
}

export default Cookie;
