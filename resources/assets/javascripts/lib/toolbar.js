import ToolbarButtonset from './toolbar_buttonset.js';
import Dialog from './dialog.js';

function getElementWidth(element) {
    var proxy = null;

    // Special case: Handle i18n hidden textareas
    // - Hidden textareas have no dimensions thus we need to proxy the
    //   width from the first visible element in the i18n group
    if ($(element).is(':hidden') && $(element).closest('.i18n_group').length > 0) {
        proxy = $(element)
            .closest('.i18n_group')
            .find('div.i18n:visible')
            .children()
            .first();
        if (proxy.length > 0) {
            element = proxy;
        }
    }

    return $(element).css('width') || $(element).outerWidth(true);
}

const Toolbar = {
    buttonSet: ToolbarButtonset,

    // Initializes (adds) a toolbar the passed textarea element
    initialize: function(element, button_set) {
        var $element = $(element),
            wrap,
            toolbar;

        // don't initialize toolbar for wysiwyg textareas
        if (STUDIP.editor_enabled && $element.hasClass('wysiwyg')) {
            return;
        }

        // Bail out if the element is not a tetarea or a toolbar has already
        // been applied
        if (!$element.is('textarea') || $element.data('toolbar-added')) {
            return;
        }

        button_set = button_set || Toolbar.buttonSet;

        // if WYSIWYG is globally enabled then add a button so
        // the user can activate it
        if (STUDIP.wysiwyg_enabled && $element.hasClass('wysiwyg')) {
            button_set.right.wysiwyg = {
                label: 'WYSIWYG',
                evaluate: function() {
                    var question = [
                        'Soll der WYSIWYG Editor aktiviert werden?'.toLocaleString(),
                        '',
                        'Die Seite muss danach neu geladen werden, um den WYSIWYG Editor zu laden.'.toLocaleString()
                    ].join('\n');
                    Dialog.confirm(question, function() {
                        var url = STUDIP.URLHelper.resolveURL('dispatch.php/wysiwyg/settings/users/current');

                        $.ajax({
                            url: url,
                            type: 'PUT',
                            contentType: 'application/json',
                            data: JSON.stringify({ disabled: false })
                        }).fail(function(xhr) {
                            window.alert(
                                [
                                    'Das Aktivieren des WYSIWYG Editors ist fehlgeschlagen.'.toLocaleString(),
                                    '',
                                    'URL'.toLocaleString() + ': ' + url,
                                    'Status'.toLocaleString() + ': ' + xhr.status + ' ' + xhr.statusText,
                                    'Antwort'.toLcoaleString() + ': ' + xhr.responseText
                                ].join('\n')
                            );
                        });
                    });
                }
            };
        }

        // Add flag so one element will never have more than one toolbar
        $element.data('toolbar-added', true);

        // Create toolbar element
        toolbar = $('<div class="buttons">');

        // Assemble toolbar
        ['left', 'right'].forEach(function(position) {
            var buttons = $('<span>').addClass(position);
            $.each(button_set[position], function(name, format) {
                var button = $('<span>').addClass(name),
                    label = format.label || name;

                if (format.icon) {
                    label = $('<img>', {
                        alt: format.label || name,
                        src: STUDIP.ASSETS_URL + 'images/icons/blue/' + format.icon + '.svg'
                    });
                }

                button
                    .html(label)
                    .button()
                    .click(function() {
                        var selection = $element.getSelection(),
                            result = format.evaluate(selection, $element, this) || selection,
                            replacement = $.isPlainObject(result)
                                ? result.replacement
                                : result === undefined
                                    ? selection
                                    : result,
                            offset = $.isPlainObject(result) ? result.offset : (result || '').length;
                        $element.replaceSelection(replacement, offset).change();
                        return false;
                    });

                buttons.append(button);
            });
            toolbar.append(buttons);
        });

        // Attach toolbar to the specified element
        wrap = $('<div class="editor_toolbar">').css({
            width: getElementWidth($element),
            display: $element.css('display')
        });
        $element
            .css('width', '100%')
            .wrap(wrap)
            .before(toolbar);
    }
};

export default Toolbar;
