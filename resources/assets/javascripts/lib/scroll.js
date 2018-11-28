/**
 * Provides means to hook into the scroll event. Registered callbacks are
 * called with the current scroll top and scroll left position so both
 * vertical and horizontal scroll events can be handled.
 *
 * Updates/calls to the callback are synchronized to screen refresh by using
 * the animation frame method (which will fallback to a timer based solution).
 */
var handlers = {},
    animId = false;

function scrollHandler() {
    var scrollTop = $(document).scrollTop(),
        scrollLeft = $(document).scrollLeft();
    $.each(handlers, function(index, handler) {
        handler(scrollTop, scrollLeft);
    });
    animId = window.requestAnimationFrame(scrollHandler);
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

const Scroll = {
    addHandler(index, handler) {
        handlers[index] = handler;
        refresh();
    },
    removeHandler(index) {
        delete handlers[index];
        refresh();
    }
};

export default Scroll;
