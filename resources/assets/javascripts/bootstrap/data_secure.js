/**
 * Secure forms or form elements by displaying a warning on page unload if
 * there are unsaved changes.
 *
 * Add the data-attribute "secure" to any <form> or :input element and when
 * the page is reloaded or the surrounding dialog is closed, a confirmation
 * dialog will appear.
 *
 * There are two config options that may be passed via the data-secure
 * attribute.
 *
 * {
 *     always: Secures the element regardless of it's changed state. If a
 *             form should always be secured, use this. If you want to exclude
 *             an element from the security check, set always on that element
 *             to false (but you should use the shorthand `data-secure="false"`
 *             since the wording "always" is a little bit misleading in this
 *             case).
 *     exists: Dynamically added nodes cannot be detected and thus will
 *             never be taken into account when detecting whether the
 *             element's value has changed. Specify a css selector that
 *             precisely identifies elements that are only present when the
 *             element needs to be secured.
 *
 * These options may be passed as a json encoded array like this:
 *
 *     <form data-secure='{always: false, exists: "#foo > .bar"}'>
 *
 * But since you will probably never need the two options at once, you may
 * either pass just a boolean value to the data-secure attribute for setting
 * the "always" option or any other non-object value as the "exists" option:
 *
 *     <form data-secure="true">
 *
 *  is equivalent to
 *
 *     <form data-secure='{always: true}'>
 *
 * and
 *
 *     <form data-secure="#foo .bar">
 *
 *  is equivalent to
 *
 *     <form data-secure='{exists: "#foo .bar"}'>
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 3.4
 */

/**
 * Normalize arbitrary input to config option object
 *
 * @param mixed input Arbitrary input
 * @return Object config
 */
function normalizeConfig(input) {
    var config = {
        always: null,
        exists: false
    };
    if ($.isPlainObject(input)) {
        config = $.extend(config, input);
    } else if (input === false || input === true) {
        config.always = input;
    } else {
        config.exists = input;
    }
    return config;
}

/**
 * Detect any changes on elements with the data-secure attribute
 * in a given context.
 *
 * @param mixed context Optional context in which the elements should be
 *                      located
 * @return bool indicating whether any changes have occured
 */
function detectChanges(context) {
    var changed = false;

    $('[data-secure]', context || document).each(function() {
        if (
            $(this)
                .closest('form')
                .data().secureSkip
        ) {
            return;
        }

        var data = $(this).data().secure;
        var config = normalizeConfig(data);
        var items = $(this).is('form') ? $(this).find(':input') : $(this);

        if (config.always === true) {
            changed = true;
        } else if (config.exists === false) {
            items
                .filter('[name]')
                .filter(':not(:checkbox,:radio)')
                .each(function() {
                    changed = changed || (this.defaultValue !== undefined && this.value !== this.defaultValue);
                });
            items
                .filter('[name]')
                .filter(':checkbox,:radio')
                .each(function() {
                    changed = changed || (this.defaultChecked !== undefined && this.checked !== this.defaultChecked);
                });
        }

        if (!changed && config.exists !== false) {
            changed = $(config.exists, this).length > 0;
        }
    });

    return changed;
}

// Secure browser window on refresh via the beforeunload event
$(window).on('beforeunload', function(event) {
    if (detectChanges() === false) {
        return;
    }

    event = event || window.event || {};
    event.returnValue = 'Ihre Eingaben wurden bislang noch nicht gespeichert.'.toLocaleString();
    return event.returnValue;
});

// Secure dialogs on close via the dialogbeforeclose event
$(document).on('dialogbeforeclose', function(event) {
    if (detectChanges(event.target) === false) {
        return true;
    }

    if (!window.confirm('Ihre Eingaben wurden bislang noch nicht gespeichert.'.toLocaleString())) {
        event.preventDefault();
        event.stopPropagation();
        return false;
    }

    return true;
});

// Mark form on submit so it will be skipped during security check
$(document)
    .on('submit', 'form[data-secure],form:has([data-secure])', function() {
        $(this)
            .closest('form')
            .data('secure-skip', true);
    })
    .on('change', 'form[data-secure],form *[data-secure]', function() {
        $(this)
            .closest('form')
            .data('secure-skip', false);
    });
