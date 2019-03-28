// Allow fieldsets to collapse
$(document).on(
    'click',
    'form.default fieldset.collapsable legend,form.default.collapsable fieldset legend',
    function() {
        $(this)
            .closest('fieldset')
            .toggleClass('collapsed');
    }
);

// Display a visible hint that indicates how many characters the user may
// input if the element has a maxlength restriction.

$(document).on('focus', 'form.default [maxlength]:not(.no-hint)', function() {
    if (!$(this).is('textarea,input') || $(this).data('length-hint') || $(this).is('[readonly],[disabled]')) {
        return;
    }

    var width = $(this).outerWidth(true),
        hint = $('<div class="length-hint">').hide(),
        wrap = $('<div class="length-hint-wrapper">').width(width),
        timeout = null;

    $(this).wrap(wrap);

    hint.text('Zeichen verbleibend: '.toLocaleString());

    hint.append('<span class="length-hint-counter">');
    hint.insertBefore(this);

    $(this)
        .focus(function() {
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                hint.finish().show('slide', { direction: 'down' }, 300);
            }, 200);
        })
        .blur(function() {
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                hint.finish().hide('slide', { direction: 'down' }, 300);
            }, 200);
        })
        .on('focus propertychange change keyup', function() {
            var count = $(this).val().length,
                max = parseInt($(this).attr('maxlength'), 10);

            hint.find('.length-hint-counter').text(max - count);
        });

    $(this).data('length-hint', true);

    setTimeout(
        function() {
            $(this).focus();
        }.bind(this),
        0
    );
});

// Automatic form submission handler when a select has changed it's value.
// Due to accessibility issues, an intuitive select[onchange=form.submit()]
// leads to terrible behaviour when invoked not by mouse. The form is
// submitted upon _every_ change, including key strokes.
// Thus, we need to overwrite this behaviour. Breakdown of this solution:
//
// - Only submit when the value has actually changed
// - Always submit when pressing enter (keycode 13)
// - Always check for change on blur event
//
// - Store whether the element was activated by click event
// - If so, submit upon next change event
// - Otherwise submit when enter has been pressed
//
// Be aware: All select[onchange*="submit()"] will be rewritten to
// select.submit-upon-select and have the onchange attribute removed.
// This might lead to unexpected behaviour.

// Ensure, every .submit-upon-select has an defaultSelected option.
$(document)
    .on('focus', 'select[onchange*="submit()"]', function() {
        $(this)
            .removeAttr('onchange')
            .addClass('submit-upon-select');
    })
    .on('click mousedown', 'select.submit-upon-select', function(event) {
        // Firefox and Chrome handle click events on selects differently,
        // thus we need the mousedown event and the click event is needed for
        // select2 elements. Please do not change!

        $(this).data('wasClicked', true);
    })
    .on('change', 'select.submit-upon-select', function(event) {
        // Trigger blur event if element was clicked in the beginning

        if ($(this).data('wasClicked')) {
            $(this).trigger('blur');
        }
    })
    .on('focusout keyup keypress keydown select', 'select.submit-upon-select', function(event) {
        var shouldSubmit = event.type === 'keyup' ? event.which === 13 : $(this).data('wasClicked'),
            is_default = $('option:selected', this).prop('defaultSelected');

        // Submit only if value has changed and either enter was pressed or
        // select was opened by click
        if (!is_default && shouldSubmit) {
            $(this)
                .closest('form')
                .submit();
            return false;
        }
    });

STUDIP.ready((event) => {
    $('.submit-upon-select', event.target).each(function() {
        var has_default_selected =
            $('option', this).filter(function() {
                return this.defaultSelected;
            }).length > 0;
        if (!has_default_selected) {
            $('option', this)
                .first()
                .prop('defaultSelected', true);
        }
    });
});


// simulate formaction attribute for input[type=image] in IE11
$(document).on('click', 'input[type=image][formaction]', function() {
    if ($(this).attr('data-confirm') === undefined) {
        $(this)
            .closest('form')
            .attr('action', $(this).attr('formaction'));
    }
});

// Use select2 for crossbrowser compliant select styling and
// handling
$.fn.select2.amd.define('select2/i18n/de', [], function() {
    return {
        inputTooLong: function(e) {
            var t = e.input.length - e.maximum;
            return 'Bitte %u Zeichen weniger eingeben'.toLocaleString().replace('%u', t);
        },
        inputTooShort: function(e) {
            var t = e.minimum - e.input.length;
            return 'Bitte %u Zeichen mehr eingeben'.toLocaleString().replace('%u', t);
        },
        loadingMore: function() {
            return 'Lade mehr Ergebnisse...'.toLocaleString();
        },
        maximumSelected: function(e) {
            var t = [
                'Sie können nur %u Eintrag auswählen'.toLocaleString(),
                'Sie können nur %u Einträge auswählen'.toLocaleString()
            ];
            return t[e.maximum === 1 ? 0 : 1].replace('%u', e.maximum);
        },
        noResults: function() {
            return 'Keine Übereinstimmungen gefunden'.toLocaleString();
        },
        searching: function() {
            return 'Suche...'.toLocaleString();
        }
    };
});
$.fn.select2.defaults.set('language', 'de');

function createSelect2(element) {
    if ($(element).data('select2')) {
        return;
    }

    var select_classes = $(element)
            .removeClass('select2-awaiting')
            .attr('class'),
        option = $('<option>'),
        width = $(element).outerWidth(true),
        cloned = $(element)
            .clone()
            .css('opacity', 0)
            .appendTo('body'),
        wrapper = $('<div class="select2-wrapper">').css('display', cloned.css('display')),
        placeholder;

    cloned.remove();
    $(wrapper)
        .add(element)
        .css('width', width);

    if ($('.is-placeholder', element).length > 0) {
        placeholder = $('.is-placeholder', element)
            .text()
            .trim();

        option.attr('selected', $(element).val() === '');
        $('.is-placeholder', element).replaceWith(option);
    }

    $(element).select2({
        adaptDropdownCssClass: function() {
            return select_classes;
        },
        allowClear: placeholder !== undefined,
        minimumResultsForSearch: $(element).closest('.sidebar').length > 0 ? 15 : 10,
        placeholder: placeholder,
        dropdownParent: $(element).closest('.ui-dialog,body'),
        templateResult: function(data, container) {
            if (data.element) {
                var option_classes = $(data.element).attr('class'),
                    element_data = $(data.element).data();
                $(container).addClass(option_classes);

                // Allow text color changes (calendar needs this)
                if (element_data.textColor) {
                    $(container).css('color', element_data.textColor);
                }
            }
            return data.text;
        },
        templateSelection: function(data, container) {
            var result = $('<span class="select2-selection__content">').text(data.text),
                element_data = $(data.element).data();
            if (element_data && element_data.textColor) {
                result.css('color', element_data.textColor);
            }

            return result;
        },
        width: 'style'
    });

    $(element)
        .next()
        .addBack()
        .wrapAll(wrapper);
}

STUDIP.ready(function () {
    // Well, this is really nasty: Select2 can't determine the select
    // element's width if it is hidden (by itself or by it's parent).
    // This is due to the fact that elements are not rendered when hidden
    // (which seems pretty obvious when you think about it) but elements
    // only have a width when they are rendered (pretty obvious as well).
    //
    // Thus, we need to handle the visible elements first and apply
    // select2 directly.
    $('select.nested-select:not(:has(optgroup)):visible').each(function() {
        createSelect2(this);
    });

    // The hidden need a little more love. The only, almost sane-ish
    // solution seems to be to attach a mutation observer to the closest
    // visible element from the requested select element and observe style,
    // class and attribute changes in order to detect when the select
    // element itself will become visible. Pretty straight forward, huh?
    $('select.nested-select:not(:has(optgroup)):hidden:not(.select2-awaiting)').each(function() {
        var observer = new window.MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if ($('select.select2-awaiting', mutation.target).length > 0) {
                    $('select.select2-awaiting', mutation.target)
                        .removeClass('select2-awaiting')
                        .each(function() {
                            createSelect2(this);
                        });
                    observer.disconnect();
                    observer = null;
                }
            });
        });
        observer.observe($(this).closest(':visible')[0], {
            attributeOldValue: true,
            attributes: true,
            attributeFilter: ['style', 'class'],
            characterData: false,
            childList: true,
            subtree: false
        });

        $(this).addClass('select2-awaiting');
    });

    // Unfortunately, this code needs to be duplicated because jQuery
    // namespacing kind of sucks. If the below change handler is namespaced
    // and we trigger that namespaced event here, still all change handlers
    // will execute (which is bad due to $(select).change(form.submit())).
    $('select:not([multiple])').each(function() {
        $(this)
            .toggleClass('has-no-value', this.value === '')
            .blur();
    });
});

$(document)
    .on('change', 'select:not([multiple])', function() {
        $(this).toggleClass('has-no-value', this.value === '');
    })
    .on('dialog-close', function(event, data) {
        $('select.nested-select:not(:has(optgroup))', data.dialog).each(function() {
            if (!$(this).data('select2')) {
                return;
            }
            $(this).select2('close');
        });
    })
    .on('select2:open', 'select', function() {
        $(this).click();
    });
