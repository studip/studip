/*jslint esversion: 6*/

/**
 * Determine whether the menu should be opened in dialog or regular layout.
 * @type {[type]}
 */
function determineBreakpoint(element) {
    return $(element).closest('.ui-dialog-content').length > 0 ? '.ui-dialog-content' : '#layout_content';
}

/**
 * Obtain all parents of the given element that have scrollable content.
 */
function getScrollableParents(element) {
    const offset = $(element).offset();
    const height = $('.action-menu-content', element).height();
    const width  = $('.action-menu-content', element).width();

    const breakpoint = determineBreakpoint(element);

    var elements = [];
    $(element).parents().each(function () {
        // Stop at breakpoint
        if ($(this).is(breakpoint)) {
            return false;
        }

        // Exit early if overflow is visible
        const overflow = $(this).css('overflow');
        if (overflow === 'visible' || overflow === 'inherit') {
            return;
        }

        // Check whether element is overflown
        const overflown = this.scrollHeight > this.clientHeight || this.scrollWidth > this.clientWidth;
        if (overflow === 'hidden' && overflown) {
            elements.push(this);
            return;
        }

        // Check if menu fits inside element
        const offs = $(this).offset();
        const w    = $(this).width();
        const h    = $(this).height();

        if (offset.left + width > offs.left + w) {
            elements.push(this);
        } else if (offset.top + height > offs.top + h) {
            elements.push(this);
        }
    });

    return elements;
}

/**
 * Scroll handler for all scroll related events.
 * This will reposition the menu(s) according to the scrolled distance.
 */
function scrollHandler(event) {
    const data = $(event.target).data('action-menu-scroll-data');

    const diff_x = event.target.scrollLeft - data.left;
    const diff_y = event.target.scrollTop - data.top;

    data.menus.forEach((menu) => {
        const offset = menu.offset();
        menu.offset({
            left: offset.left - diff_x,
            top: offset.top - diff_y
        });
    });

    data.left = event.target.scrollLeft;
    data.top  = event.target.scrollTop;

    $(event.target).data('action-menu-scroll-data', data);
}

const stash  = new Map();
const secret = Symbol();

class ActionMenu {
    /**
     * Create menu using a singleton pattern for each element.
     */
    static create(element, position = true) {
        const id = $(element).uniqueId().attr('id');
        const breakpoint = determineBreakpoint(element);
        if (!stash.has(id)) {
            const menu_offset = $(element).offset().top + $('.action-menu-content', element).height();
            const max_offset = $(breakpoint).offset().top + $(breakpoint).height();
            const reversed = menu_offset > max_offset;

            stash.set(id, new ActionMenu(secret, element, reversed, position));
        }

        return stash.get(id);
    }

    /**
     * Closes all menus.
     * @return {[type]} [description]
     */
    static closeAll() {
        stash.forEach((menu) => menu.close());
    }

    /**
     * Private constructor by implementing the secret/passed_secret mechanism.
     */
    constructor(passed_secret, element, reversed, position) {
        // Enforce use of create (would use a private constructor if I could)
        if (secret !== passed_secret) {
            throw new Error('Cannot create ActionMenu. Use ActionMenu.create()!');
        }

        const offset     = $(element).offset();
        const height     = $('.action-menu-content').height();
        const width      = $('.action-menu-content').width();
        const breakpoint = determineBreakpoint(element);

        this.element = $(element);
        this.menu = this.element;
        this.content = $('.action-menu-content', element);
        this.is_reversed = reversed;
        this.is_open = false;

        // Reposition the menu?
        if (position) {
            var parents = getScrollableParents(this.element);
            if (parents.length > 0) {
                this.menu = $('<div class="action-menu-wrapper">').append(this.content.remove());
                $('.action-menu-icon', element).clone().data('action-menu-element', element).prependTo(this.menu);

                this.menu
                    .offset(this.element.offset())
                    .appendTo(breakpoint);

                // Always add breakpoint
                parents.push(breakpoint);
                parents.forEach((parent, index) => {
                    var data = $(parent).data('action-menu-scroll-data') || {
                        menus: [],
                        left: parent.scrollLeft,
                        top: parent.scrollTop
                    };
                    data.menus.push(this.menu);

                    $(parent).data('action-menu-scroll-data', data);

                    if (data.menus.length < 2) {
                        $(parent).scroll(scrollHandler);
                    }
                });
            }
        }

        this.update();
    }

    /**
     * Adds a class to the menu's element.
     */
    addClass(name) {
        this.menu.addClass(name);
    }

    /**
     * Open the menu.
     */
    open() {
        this.toggle(true);
    }

    /**
     * Close the menu.
     */
    close() {
        this.toggle(false);
    }

    /**
     * Toggle the menus display state. Pass a state to enforce it.
     */
    toggle(state = null) {
        this.is_open = state === null ? !this.is_open : state;

        this.update();
    }

    /**
     * Update the menu element's attributes.
     */
    update() {
        this.element.toggleClass('is-open', this.is_open);

        this.menu.toggleClass('is-open', this.is_open);
        this.menu.toggleClass('is-reversed', this.is_reversed);
        this.menu.attr('aria-expanded', this.is_open ? 'true' : 'false');
    }
}

export default ActionMenu;
