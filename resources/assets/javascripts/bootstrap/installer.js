/*jslint esversion: 6*/

function domReady(fn) {
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        setTimeout(fn, 1);
    } else {
        document.addEventListener('DOMContentLoaded', fn);
    }
}

domReady(() => {
    if (!('fetch' in window)) {
        const hidden_input = document.createElement('input');
        hidden_input.setAttribute('type', 'hidden');
        hidden_input.setAttribute('name', 'basic');
        hidden_input.setAttribute('value', 1);
        document.querySelector('form').append(hidden_input);

        return;
    }

    var requests = [];
    document.querySelectorAll('dl.requests > dt[data-request-url]').forEach((element) => {
        requests.push({
            element: element,
            url: element.dataset.requestUrl
        });
    });

    function next() {
        if (requests.length === 0) {
            return;
        }
        const current = requests.shift();

        current.element.classList.add('requesting');
        fetch(current.url, {
            cache: 'no-cache',
            credentials: 'same-origin'
        }).then(response => {
            current.element.classList.remove('requesting');
            if (!response.ok) {
                current.element.classList.add('failed');
                response.json().then(data => {
                    current.element.nextElementSibling.nextElementSibling.querySelector('.response').innerText = data;
                });
            } else {
                current.element.classList.add('succeeded');
                next();
            }
        });
    }

    next();
});
