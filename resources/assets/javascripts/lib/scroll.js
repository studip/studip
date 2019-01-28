/**
 * Provides means to hook into the scroll event. Registered callbacks are
 * called with the current scroll top and scroll left position so both
 * vertical and horizontal scroll events can be handled.
 *
 * Updates/calls to the callback are synchronized to screen refresh by using
 * the animation frame method (which will fallback to a timer based solution).
 */
var handlers = {};
var animId = false;

var lastTop  = null;
var lastLeft = null;

function scrollHandler() {
    var scrollTop = $(document).scrollTop();
    var scrollLeft = $(document).scrollLeft();

    if (scrollTop !== lastTop || scrollLeft !== lastLeft) {
        $.each(handlers, function(index, handler) {
            handler(scrollTop, scrollLeft);
        });
    }

    animId = false;

    engageScrollTrigger();
}

function refresh() {
    var hasHandlers = !$.isEmptyObject(handlers);
    if (!hasHandlers && animId !== false) {
        window.cancelAnimationFrame(animId);
        animId = false;
    } else if (hasHandlers && animId === false) {
        animId = window.requestAnimationFrame(scrollHandler);
    }
}

function engageScrollTrigger() {
    $(window).off('scroll.studip-handler');
    $(window).one('scroll.studip-handler', refresh);
}

const Scroll = {
    addHandler(index, handler) {
        handlers[index] = handler;
        engageScrollTrigger();
    },
    removeHandler(index) {
        delete handlers[index];
        engageScrollTrigger();
    }
};

export default Scroll;
