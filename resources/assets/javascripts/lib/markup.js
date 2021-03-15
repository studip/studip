/* ------------------------------------------------------------------------
 * Javascript-spezifisches Markup
 * ------------------------------------------------------------------------ */

const Markup = {
    element (selector) {
        var elements;
        if (typeof selector === 'string' && document.getElementById(selector)) {
            elements = $('#' + selector);
        } else {
            elements = $(selector);
        }
        elements.each((index, element) => {
            $.each(Markup.callbacks, (index, func) => {
                if (index !== 'element' || typeof func === 'function') {
                    func(element);
                }
            });
        });
    },
    callbacks: {
        math_jax (element) {
            const elements = $('span.math-tex:not(:has(.MathJax))', element);
            $('.formatted-content', element).filter((idx, elm) => {
                // Regular [tex] expression
                if ($(elm).is(':contains("[tex]")')) {
                    return true;
                }

                // $...$ or $$...$$ expression
                if (elm.innerText.match(/\${1,2}.+\${1,2}/)) {
                    return true;
                }

                // \(...\) expression
                if (elm.innerText.match(/\\\(.+\\\)/)) {
                    return true;
                }

                return false;
            }).add(elements).each((index, block) => {
                STUDIP.loadChunk('mathjax').then(( MathJax ) => {
                    MathJax.Hub.Queue(['Typeset', MathJax.Hub, block]);
                });
            });
        },
        codehighlight (element) {
            $('pre.usercode:not(.hljs)', element).each(function (index, block) {
                STUDIP.loadChunk('code-highlight').then((hljs) => {
                    hljs.highlightBlock(block);
                });
            });
        }
    }
};

export default Markup;
