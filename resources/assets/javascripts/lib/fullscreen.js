/*jslint esversion: 6*/
const Fullscreen = {
    toggle () {
        if (sessionStorage.getItem('studip-fullscreen') === 'on') {
            STUDIP.Fullscreen.leave();
        } else {
            STUDIP.Fullscreen.enter();
        }
    },

    enter (immediate = false) {
        // Set appropriate class on html element to trigger fullscreen mode and
        // transisitions
        $('html').addClass('is-fullscreen').toggleClass('is-fullscreen-immediately', immediate);

        // Move toggle element into viewport
        $('.fullscreen-toggle').prependTo('#layout_content');

        // Attach key handler that allows keypress on escape to leave fullscreen
        $(document).on('keydown.key27', (event) => {
            if (event.key === 'Escape') {
                STUDIP.Fullscreen.leave();
            }
        });

        // Store indicator in session
        sessionStorage.setItem('studip-fullscreen', 'on');
    },

    leave () {
        // Remove indicator from session
        sessionStorage.removeItem('studip-fullscreen');

        // Deactivate key handler
        $(document).off('keydown.key27');

        // Move toggle element into secondary navigation
        $('.fullscreen-toggle').insertBefore('.helpbar-container');

        //
        (new Promise((resolve, reject) => {
            var timeout = setTimeout(() => {
                $('#layout-sidebar').off('transitionend');
                resolve();
            }, 500);
            $('#layout-sidebar').one('transitionend', () => {
                clearTimeout(timeout);
                resolve();
            });
        })).then(() => {
            $(document.body).trigger('sticky_kit:recalc');
        });


        // Remove classes on html element
        $('html').removeClass('is-fullscreen is-fullscreen-immediately');
    }
};

export default Fullscreen;
