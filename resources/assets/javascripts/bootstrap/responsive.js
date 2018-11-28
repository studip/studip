var media_query = window.matchMedia('(max-width: 767px)');

// Builds a dom element from a navigation object
function buildMenu(navigation, path, id) {
    var list = $('<ul>');

    if (id) {
        list.attr('id', id);
    }

    // TODO: Templating?
    _.forEach(navigation, function(nav, node) {
        nav.url = STUDIP.URLHelper.getURL(nav.url, {}, true);
        var subpath = path + '_' + node,
            li = $('<li class="navigation-item">'),
            title = $('<div class="nav-title">').appendTo(li),
            link = $('<a>')
                .text(nav.title)
                .attr('href', nav.url)
                .appendTo(title),
            icon = nav.icon || false;

        if (icon) {
            if (!icon.match(/^https?:\/\//)) {
                icon = STUDIP.ASSETS_URL + icon;
            }
            $('<img class="icon">')
                .attr('src', icon)
                .prependTo(link);
        }

        if (nav.children) {
            $('<input type="checkbox">')
                .attr('id', subpath)
                .prop('checked', nav.active)
                .appendTo(li);
            $('<label class="nav-label">')
                .attr('for', subpath)
                .text(' ')
                .appendTo(li);
            li.append(buildMenu(nav.children, subpath));
        }

        list.append(li);
    });

    return list;
}

// Adds the responsive menu to the dom
function addMenu() {
    var wrapper = $('<div id="responsive-container">'),
        menu = buildMenu(STUDIP.Navigation, 'resp', 'responsive-navigation');

    $('<label for="responsive-toggle">').appendTo(wrapper);
    $('<input type="checkbox" id="responsive-toggle">').appendTo(wrapper);
    wrapper.append(menu);

    $('<li>', { html: wrapper }).prependTo('#barBottomright > ul');
}

// Responsifies the layout. Builds the responsive menu from existing
// STUDIP.Navigation object
function responsify() {
    media_query.removeListener(responsify);

    $('html').addClass('responsified');

    addMenu();

    if ($('#layout-sidebar > section').length > 0) {
        $('<li id="sidebar-menu">')
            .on('click', STUDIP.Sidebar.open)
            .appendTo('#barBottomright > ul');

        $('#responsive-toggle').on('change', function() {
            $('#layout-sidebar').removeClass('visible-sidebar');
            $('#responsive-navigation').toggleClass('visible', this.checked);
        });
    }

    $('#responsive-navigation :checkbox')
        .on('change', function() {
            var li = $(this).closest('li');
            if ($(this).is(':checked')) {
                li.siblings()
                    .find(':checkbox:checked')
                    .prop('checked', false);
            }

            // Force redraw of submenu (at least ios safari/chrome would
            // not show it without a forced redraw)
            $(this)
                .siblings('ul')
                .hide(0, function() {
                    $(this).show();
                });
        })
        .reverse()
        .trigger('change');

    var sidebar_avatar_menu = $('<div class="sidebar-widget sidebar-avatar-menu">'),
        avatar_menu = $('#header_avatar_menu'),
        title = $('.action-menu-title', avatar_menu).text(),
        list = $('<ul class="widget-list widget-links">');
    $('<div class="sidebar-widget-header">')
        .text(title)
        .appendTo(sidebar_avatar_menu);

    $('.action-menu-item', avatar_menu).each(function() {
        var src = $('img', this).attr('src'),
            link = $('a', this).clone();

        link.find('img').remove();

        $('<li>')
            .append(link)
            .css({
                backgroundSize: '16px',
                backgroundImage: 'url(' + src + ')'
            })
            .appendTo(list);
    });

    $('<div class="sidebar-widget-content">')
        .append(list)
        .appendTo(sidebar_avatar_menu);

    $('#layout-sidebar > .sidebar').prepend(sidebar_avatar_menu);
}

function setResponsiveDisplay(state) {
    if (state === undefined) {
        state = true;
    }

    $('html').toggleClass('responsive-display', state);
    STUDIP.Sidebar.setSticky(!state);

    if (state) {
        STUDIP.HeaderMagic.disable();
    } else {
        STUDIP.HeaderMagic.enable();
    }
}

// Build responsive menu on domready or resize
$(document).ready(function() {
    if (media_query.matches) {
        responsify();
        setResponsiveDisplay();
    } else {
        media_query.addListener(responsify);
    }

    media_query.addListener(function() {
        setResponsiveDisplay(media_query.matches);
    });
});

// Trigger search in responsive display
$(document)
    .on('click', '#quicksearch .quicksearchbutton', function() {
        if ($('html').is(':not(.responsive-display)') || $('#quicksearch').is('.open')) {
            return;
        }

        $('#quicksearch').addClass('open');
        $('.quicksearchbox').focus();

        return false;
    })
    .on('blur', '#quicksearch.open .quicksearchbox', function() {
        if (!this.value.trim().length) {
            $('#quicksearch').removeClass('open');
        }
    })
    .on('autocompleteopen', function(event) {
        if ($(event.target).closest('#quicksearch').length === 0) {
            return;
        }
        $('body > .ui-autocomplete').css({
            left: 0,
            right: 0,
            boxSizing: 'border-box'
        });
    });
