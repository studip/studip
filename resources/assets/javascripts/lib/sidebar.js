import Scroll from './scroll.js';

const Sidebar = {};

Sidebar.open = function() {
    $('#responsive-toggle').prop('checked', false);
    $('#responsive-navigation').removeClass('visible');
    $('#layout-sidebar').toggleClass('visible-sidebar');
};

// This function inits the sticky sidebar by using the StickyKit lib
// <http://leafo.net/sticky-kit/>
Sidebar.setSticky = function(is_sticky) {
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
export default Sidebar;
