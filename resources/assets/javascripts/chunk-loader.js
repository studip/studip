/*jslint esversion: 6*/
STUDIP.loadScript = function (script_name) {
    return new Promise(function (resolve, reject) {
        let script = document.createElement('script');
        script.src = `${STUDIP.ASSETS_URL}/${script_name}`;
        script.onload = resolve;
        script.onerror = reject;
        document.head.appendChild(script);
    });
};

STUDIP.loadChunk = (function () {
    var mathjax_promise = null;

    return function (chunk) {
        var promise = null;
        switch (chunk) {

            case 'code-highlight':
                promise = import(
                    /* webpackChunkName: "code-highlight" */
                    './chunks/code-highlight'
                ).then(({default: hljs}) => {
                    return hljs;
                });
                break;

            case 'tablesorter':
                promise = import(
                    /* webpackChunkName: "tablesorter" */
                    './chunks/tablesorter'
                );
                break;

            case 'widgetsystem':
                promise = import(
                    /* webpackChunkName: "widgetsystem" */
                    './chunks/widgetsystem'
                );
                break;

            case 'mathjax':
                if (mathjax_promise === null) {
                    mathjax_promise = STUDIP.loadScript(
                        'javascripts/mathjax/MathJax.js?config=TeX-AMS_HTML,default'
                    ).then(() => {
                        (function (origPrint) {
                            window.print = function () {
                                MathJax.Hub.Queue(
                                    ['Delay', MathJax.Callback, 700],
                                    origPrint
                                );
                            };
                        })(window.print);

                        mathjax_loaded = true;

                        return MathJax;
                    }).catch(() => {
                        mathjax_loaded = false;
                    });
                }
                promise = mathjax_promise;
                break;

            default:
                promise = Promise.reject('Unknown chunk');
        }

        return promise.catch((error) => {
            console.error(`Could not load chunk ${chunk}`, error);
        });
    };
}());
