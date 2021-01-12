/*jslint esversion: 6*/

function domReady(fn) {
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        setTimeout(fn, 1);
    } else {
        document.addEventListener('DOMContentLoaded', fn);
    }
}

domReady(() => {
    if (!('fetch' in window) || !('Promise' in window)) {
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
            url: element.dataset.requestUrl,
            event_source: element.dataset.eventSource !== undefined
        });
    });

    const submit_button = document.querySelector('form button[type="submit"].button');
    submit_button.disabled = true;

    function next() {
        if (requests.length === 0) {
            submit_button.disabled = false;
            return;
        }
        const current = requests.shift();
        var promise;

        current.element.classList.add('requesting');

        if (current.event_source && 'EventSource' in window) {
            const notifier = document.createElement('div');
            notifier.setAttribute('data-percent', 0);

            promise = new Promise((resolve, reject) => {
                current.element.classList.add('event-sourced');

                const progress = current.element.nextElementSibling.nextElementSibling.nextElementSibling;
                var total = 0;

                progress.insertAdjacentElement('afterend', notifier);
                notifier.setAttribute(
                    'style',
                    `left: ${progress.offsetLeft}px; top: ${progress.offsetTop}px`
                );

                const evtSource = new EventSource(current.url + '?evts=1', {
                    withCredentials: true
                });
                evtSource.addEventListener('total', (event) => {
                    total = parseInt(event.data, 10);
                    progress.setAttribute('max', total);
                });
                evtSource.addEventListener('file', (event) => {
                    notifier.setAttribute('data-file', event.data);
                });
                evtSource.addEventListener('current', (event) => {
                    let current = parseInt(event.data, 10);
                    progress.setAttribute('value', current);
                    notifier.setAttribute('data-percent', (100 * current / total).toFixed(2));
                });
                evtSource.addEventListener('error', (event) => {
                    evtSource.close();
                    reject(event.data || 'Fehler beim Installieren');
                });
                evtSource.addEventListener('close', (event) => {
                    evtSource.close();
                    resolve();
                });
            });

            promise.finally(() => {
                if (notifier.parentNode) {
                    notifier.parentNode.removeChild(notifier);
                }
                current.element.classList.remove('event-sourced');
            });
        } else {
            promise = fetch(current.url, {
                cache: 'no-cache',
                credentials: 'same-origin'
            }).then(response => {
                if (!response.ok) {
                    return response.json().then(message => {
                        return Promise.reject(message);
                    });
                }
            });
        }

        promise.then(response => {
            current.element.classList.add('succeeded');
            next();
        }).catch(error => {
            current.element.classList.add('failed');

            if (error !== null && error === Object(error)) {
                current.element.nextElementSibling.nextElementSibling.querySelectorAll('.response').forEach((element) => {
                    let key = element.dataset.key;
                    element.value = error[key];
                });
            } else {
                current.element.nextElementSibling.nextElementSibling.querySelector('.response').innerText = error;
            }
        }).finally(() => {
            current.element.classList.remove('requesting');
        });
    }

    next();
});
