var media_query = window.matchMedia('(max-width: 767px)');

// Builds a dom element from a navigation object
function buildMenu(navigation, path, id, activated) {
    var list = $('<ul>');

    if (id) {
        list.attr('id', id);
    }

    // TODO: Templating?
    _.forEach(navigation, function(nav, node) {
        nav.url = STUDIP.URLHelper.getURL(nav.url, {}, true);
        let subpath = path ? `${path}/${node}` : node;
        let li = $('<li class="navigation-item">');
        let title = $('<div class="nav-title">').appendTo(li);
        let link = $(`<a href="${nav.url}">`).text(nav.title).appendTo(title);

        if (nav.icon) {
            if (!nav.icon.match(/^https?:\/\//)) {
                nav.icon = STUDIP.ASSETS_URL + nav.icon;
            }
            $(link).prepend(`<img class="icon" src="${nav.icon}">`)
        }

        if (nav.children) {
            let active = activated.indexOf(subpath) !== -1;
            $(`<input type="checkbox" id="resp/${subpath}">`)
                .prop('checked', active)
                .appendTo(li);
            li.append(
                `<label class="nav-label" for="resp/${subpath}"> </label>`,
                buildMenu(nav.children, subpath, false, activated)
            );
        }

        list.append(li);
    });

    return list;
}

// Adds the responsive menu to the dom
function addMenu() {
    let wrapper = $('<div id="responsive-container">').append(
        '<label for="responsive-toggle">',
        '<input type="checkbox" id="responsive-toggle">',
        buildMenu(
            STUDIP.Navigation.navigation,
            false,
            'responsive-navigation',
            STUDIP.Navigation.activated
        )
    );

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
}

function setResponsiveDisplay(state = true) {
    $('html').toggleClass('responsive-display', state);
    STUDIP.Sidebar.setSticky(!state);

    if (state) {
        STUDIP.HeaderMagic.disable();
    } else {
        STUDIP.HeaderMagic.enable();
    }
}

// Build responsive menu on domready or resize
STUDIP.domReady(() => {
    const cache = STUDIP.Cache.getInstance('responsive.');
    if (STUDIP.Navigation.navigation !== undefined) {
        cache.set('navigation', STUDIP.Navigation.navigation);
        STUDIP.Cookie.set('responsive-navigation-hash', STUDIP.Navigation.hash);
    } else {
        STUDIP.Navigation.navigation = cache.get('navigation');
    }

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
$(document).on('click', '#quicksearch .quicksearchbutton', function() {
    if ($('html').is(':not(.responsive-display)') || $('#quicksearch').is('.open')) {
        return;
    }

    $('#quicksearch').addClass('open');
    $('.quicksearchbox').focus();

    return false;
}).on('blur', '#quicksearch.open .quicksearchbox', function() {
    if (!this.value.trim().length) {
        $('#quicksearch').removeClass('open');
    }
}).on('autocompleteopen', function(event) {
    if ($(event.target).closest('#quicksearch').length === 0) {
        return;
    }
    $('body > .ui-autocomplete').css({
        left: 0,
        right: 0,
        boxSizing: 'border-box'
    });
});
