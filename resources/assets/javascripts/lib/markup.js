/* ------------------------------------------------------------------------
 * Javascript-spezifisches Markup
 * ------------------------------------------------------------------------ */

const Markup = {
    element: function(selector) {
        var elements;
        if (document.getElementById(selector)) {
            elements = jQuery('#' + selector);
        } else {
            elements = jQuery(selector);
        }
        elements.each(function(index, element) {
            jQuery.each(Markup.callbacks, function(index, func) {
                if (index !== 'element' || typeof func === 'function') {
                    func(element);
                }
            });
        });
    },
    callbacks: {
        math_jax: function(element) {
            MathJax.Hub.Queue(['Typeset', MathJax.Hub, element]);
        },
        codehighlight: function(element) {
            jQuery('pre.usercode:not(.hljs)').each(function (index, block) {
                STUDIP.loadChunk('code-highlight').then((hljs) => {
                    hljs.highlightBlock(block);
                });
            });
        }
    }
};

export default Markup;
