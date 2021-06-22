/*jslint esversion: 6*/

import HeaderMagic from './header_magic.js';
import Sidebar from './sidebar.js';

const Responsive = {
    media_query: window.matchMedia('(max-width: 767px)'),

    // Builds a dom element from a navigation object
    buildMenu (navigation, id, activated) {
        var list = $('<ul>');

        if (id) {
            list.attr('id', id);
        }

        // TODO: Templating?
        _.forEach(navigation, (nav, node) => {
            nav.url = STUDIP.URLHelper.getURL(nav.url, {}, true);
            let li = $('<li class="navigation-item">');
            let title = $('<div class="nav-title">').appendTo(li);
            let link = $(`<a href="${nav.url}">`).text(nav.title).appendTo(title);

            if (nav.icon) {
                if (!nav.icon.match(/^https?:\/\//)) {
                    nav.icon = STUDIP.ASSETS_URL + nav.icon;
                }
                $(link).prepend(`<img class="icon" src="${nav.icon}">`);
            }

            if (nav.children) {
                let active = activated.indexOf(node) !== -1;
                $(`<input type="checkbox" id="resp/${node}">`)
                    .prop('checked', active)
                    .appendTo(li);
                li.append(
                    `<label class="nav-label" for="resp/${node}"> </label>`,
                    Responsive.buildMenu(nav.children, false, activated)
                );
            }

            list.append(li);
        });

        return list;
    },

    // Adds the responsive menu to the dom
    addMenu () {
        let wrapper = $('<div id="responsive-container">').append(
            '<label for="responsive-toggle">',
            '<input type="checkbox" id="responsive-toggle">',
            Responsive.buildMenu(
                STUDIP.Navigation.navigation,
                'responsive-navigation',
                STUDIP.Navigation.activated
            ),
            '<label for="responsive-toggle">'
        );

        $('<li>', { html: wrapper }).prependTo('#barBottomright > ul');
    },

    // Responsifies the layout. Builds the responsive menu from existing
    // STUDIP.Navigation object
    responsify () {
        Responsive.media_query.removeListener(Responsive.responsify);

        $('html').addClass('responsified');

        Responsive.addMenu();

        if ($('#layout-sidebar > section').length > 0) {
            $('<li id="sidebar-menu">')
                .on('click', () => Sidebar.open())
                .appendTo('#barBottomright > ul');

            $('<label id="sidebar-shadow-toggle">')
                .on('click', () => Sidebar.close())
                .prependTo('#layout-sidebar');

            $('#responsive-toggle').on('change', function() {
                $('#layout-sidebar').removeClass('visible-sidebar');
                $('#responsive-navigation').toggleClass('visible', this.checked);
            });
        } else {
            $('#responsive-toggle').on('change', function() {
                $('#responsive-navigation').toggleClass('visible', this.checked);
            });
        }

        $('#responsive-navigation :checkbox').on('change', function () {
            let li = $(this).closest('li');
            if ($(this).is(':checked')) {
                li.siblings().find(':checkbox:checked').prop('checked', false);
            }

            // Force redraw of submenu (at least ios safari/chrome would
            // not show it without a forced redraw)
            $(this).siblings('ul').hide(0, function () {
                $(this).show();
            });
        }).reverse().trigger('change');

        var sidebar_avatar_menu = $('<div class="sidebar-widget sidebar-avatar-menu">');
        var avatar_menu = $('#header_avatar_menu');
        var title = $('.action-menu-title', avatar_menu).text();
        var list = $('<ul class="widget-list widget-links">');
        $('<div class="sidebar-widget-header">').text(title).appendTo(sidebar_avatar_menu);

        $('.action-menu-item', avatar_menu).each(function() {
            var src = $('img', this).attr('src');
            var link = $('a', this).clone();

            link.find('img').remove();

            $('<li>').append(link).css({
                backgroundSize: '16px',
                backgroundImage: `url(${src})`
            }).appendTo(list);
        });

        $('<div class="sidebar-widget-content">')
            .append(list)
            .appendTo(sidebar_avatar_menu);

        $('#layout-sidebar > .sidebar').prepend(sidebar_avatar_menu);
    },

    setResponsiveDisplay (state = true) {
        $('html').toggleClass('responsive-display', state);

        if (state) {
            Sidebar.disableSticky();
            HeaderMagic.disable();
        } else {
            Sidebar.enableSticky();
            HeaderMagic.enable();
        }
    },

    engage () {
        if (Responsive.media_query.matches) {
            Responsive.responsify();
            Responsive.setResponsiveDisplay();
        } else {
            Responsive.media_query.addListener(Responsive.responsify);
        }

        Responsive.media_query.addListener(() => {
            Responsive.setResponsiveDisplay(Responsive.media_query.matches);
        });
    }
};

export default Responsive;
