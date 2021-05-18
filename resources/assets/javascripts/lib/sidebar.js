import Scroll from './scroll.js';

const Sidebar = {
    stickyEnabled: true,
    enableSticky() {
        this.stickyEnabled = true;
        this.setSticky();
    },
    disableSticky() {
        this.stickyEnabled = false;
        this.setSticky(false);
    },
    open () {
        this.toggle(true);
    },
    close () {
        this.toggle(false);
    },
    toggle (visible = null) {
        visible = visible ?? !$('#layout-sidebar').hasClass('visible-sidebar');

        // Hide navigation
        $('#responsive-toggle').prop('checked', false);
        $('#responsive-navigation').removeClass('visible');

        $('#layout-sidebar').toggleClass('visible-sidebar', visible);
    }
};

// This function inits the sticky sidebar by using the StickyKit lib
// <http://leafo.net/sticky-kit/>
Sidebar.setSticky = function(is_sticky) {
    if (!this.stickyEnabled) {
        return;
    }

    if (is_sticky === undefined || is_sticky) {
        $('#layout-sidebar .sidebar')
            .stick_in_parent({
                offset_top: $('#barBottomContainer').outerHeight(true) + 15,
                inner_scrolling: true
            })
            .on('sticky_kit:stick sticky_kit:unbottom', function() {
                var stuckHandler = function(top, left) {
                    $('#layout-sidebar .sidebar').css('margin-left', -left);
                };
                Scroll.addHandler('sticky.horizontal', stuckHandler);
                stuckHandler(0, $(window).scrollLeft());
            })
            .on('sticky_kit:unstick sticky_kit:bottom', function() {
                Scroll.removeHandler('sticky.horizontal');
                $(this).css('margin-left', 0);
            });
    } else {
        Scroll.removeHandler('sticky.horizontal');
        $('#layout-sidebar .sidebar')
            .trigger('sticky_kit:unstick')
            .trigger('sticky_kit:detach');
    }
};

Sidebar.checkActiveLineHeight = () => {
    $('#layout-sidebar .sidebar .sidebar-widget-content .widget-links li.active a.active').each(function() {
        var link = $(this);
        var actual_text = link.text();
        link.text('tmp');
        var default_height = link.outerHeight();
        link.text(actual_text);
        var actual_height = link.outerHeight();
        if (actual_height > default_height) { //it is rendered in more lines
            link.css('line-height', '20px');
        }
    });
}
export default Sidebar;
