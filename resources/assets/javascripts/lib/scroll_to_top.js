import Scroll from './scroll.js';

let fold;
let was_below_the_fold = false;

const back_to_top = function(scrolltop) {
    var is_below_the_fold = scrolltop > fold;
    if (is_below_the_fold !== was_below_the_fold) {
        $('#scroll-to-top').toggleClass('hide', !is_below_the_fold);
        was_below_the_fold = is_below_the_fold;
    }
};

const ScrollToTop = {
    enable() {
        var minScrollHeight = Math.min(
            document.body.scrollHeight, document.documentElement.scrollHeight,
            document.body.offsetHeight, document.documentElement.offsetHeight,
            document.body.clientHeight, document.documentElement.clientHeight
        );
        fold = minScrollHeight - (minScrollHeight / 5); // top of fifth portion!
        Scroll.addHandler('back-to-top', back_to_top);
    },
    disable() {
        Scroll.removeHandler('header');
        $('#scroll-to-top').addClass('hide');
    },
    moveBack() {
        $('#scroll-to-top').on('click', function(e) {
            $('html, body').stop().animate({
                scrollTop: (0)
            }, 1000, 'easeInOutExpo');
            e.preventDefault();
        });
    }
};

export default ScrollToTop;
