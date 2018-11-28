const SkipLinks = {
    activeElement: null,
    navigationStatus: 0,

    /**
     * Displays the skip link navigation after first hitting the tab-key
     * @param event: event-object of type keyup
     */
    showSkipLinkNavigation: function(event) {
        if (event.keyCode === 9) {
            //tab-key
            SkipLinks.moveSkipLinkNavigationIn();
            jQuery('.focus_box').removeClass('focus_box');
        }
    },

    /**
     * shows the skiplink-navigation window by moving it from the left
     */
    moveSkipLinkNavigationIn: function() {
        if (SkipLinks.navigationStatus === 0) {
            var VpWidth = jQuery(window).width();
            jQuery('#skip_link_navigation li:first a').focus();
            jQuery('#skip_link_navigation').css({ left: VpWidth / 2, opacity: 0 });
            jQuery('#skip_link_navigation').animate({ opacity: 1.0 }, 500);
            SkipLinks.navigationStatus = 1;
        }
    },

    /**
     * removes the skiplink-navigation window by moving it out of viewport
     */
    moveSkipLinkNavigationOut: function() {
        if (SkipLinks.navigationStatus === 1) {
            jQuery(SkipLinks.box).hide();
            jQuery('#skip_link_navigation').animate({ opacity: 0 }, 500, function() {
                jQuery(this).css('left', '-600px');
            });
        }
        SkipLinks.navigationStatus = 2;
    },

    getFragment: function() {
        var fragmentStart = document.location.hash.indexOf('#');
        if (fragmentStart < 0) {
            return '';
        }
        return document.location.hash.substring(fragmentStart);
    },

    /**
     * Inserts the list with skip links
     */
    insertSkipLinks: function() {
        jQuery('#skip_link_navigation').prepend(jQuery('#skiplink_list'));
        jQuery('#skiplink_list').show();
        jQuery('#skip_link_navigation').attr('aria-busy', 'false');
        jQuery('#skip_link_navigation').attr('tabindex', '-1');
        SkipLinks.insertHeadLines();
        return false;
    },

    /**
     * sets the area (of the id) as the current area for tab-navigation
     * and highlights it
     */
    setActiveTarget: function(id) {
        var fragment = null;
        // set active area only if skip links are activated
        if (!jQuery('*').is('#skip_link_navigation')) {
            return false;
        }
        if (id) {
            fragment = id;
        } else {
            fragment = SkipLinks.getFragment();
        }
        if (jQuery('*').is(fragment) && fragment.length > 0 && fragment !== SkipLinks.activeElement) {
            SkipLinks.moveSkipLinkNavigationOut();
            jQuery('.focus_box').removeClass('focus_box');
            jQuery(fragment).addClass('focus_box');
            jQuery(fragment)
                .attr('tabindex', '-1')
                .click()
                .focus();
            SkipLinks.activeElement = fragment;
            return true;
        } else {
            jQuery('#skip_link_navigation li a')
                .first()
                .focus();
        }
        return false;
    },

    injectAriaRoles: function() {
        jQuery('#main_content').attr({
            role: 'main',
            'aria-labelledby': 'main_content_landmark_label'
        });
        jQuery('#layout_content').attr({
            role: 'main',
            'aria-labelledby': 'layout_content_landmark_label'
        });
        jQuery('#layout_infobox').attr({
            role: 'complementary',
            'aria-labelledby': 'layout_infobox_landmark_label'
        });
    },

    insertHeadLines: function() {
        var target = null;
        jQuery('#skip_link_navigation a').each(function() {
            target = jQuery(this).attr('href');
            if (jQuery(target).is('li,td')) {
                jQuery(target).prepend(
                    '<h2 id="' +
                        jQuery(target).attr('id') +
                        '_landmark_label" class="skip_target">' +
                        jQuery(this).text() +
                        '</h2>'
                );
            } else {
                jQuery(target).before(
                    '<h2 id="' +
                        jQuery(target).attr('id') +
                        '_landmark_label" class="skip_target">' +
                        jQuery(this).text() +
                        '</h2>'
                );
            }
            jQuery(target).attr('aria-labelledby', jQuery(target).attr('id') + '_landmark_label');
        });
    },

    initialize: function() {
        SkipLinks.insertSkipLinks();
        SkipLinks.injectAriaRoles();
        SkipLinks.setActiveTarget();
    }
};

export default SkipLinks;
