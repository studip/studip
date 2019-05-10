/**
 * wysiwyg.js - Replace HTML textareas with WYSIWYG editor.
 *
 * Developer documentation can be found at
 * http://docs.studip.de/develop/Entwickler/Wysiwyg.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Robert Costa <zabbarob@gmail.com>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */
import parseOptions from './parse_options.js';

const wysiwyg = {
    disabled: !STUDIP.editor_enabled,
    // NOTE keep this function in sync with Markup class
    htmlMarker: '<!--HTML-->',
    htmlMarkerRegExp: /^\s*<!--\s*HTML.*?-->/i,

    isHtml: function isHtml(text) {
        // NOTE keep this function in sync with
        // Markup::isHtml in Markup.class.php
        return this.hasHtmlMarker(text);
    },
    hasHtmlMarker: function hasHtmlMarker(text) {
        // NOTE keep this function in sync with
        // Markup::hasHtmlMarker in Markup.class.php
        return this.htmlMarkerRegExp.test(text);
    },
    markAsHtml: function markAsHtml(text) {
        // NOTE keep this function in sync with
        // Markup::markAsHtml in Markup.class.php
        if (this.hasHtmlMarker(text) || text.trim() == '') {
            return text; // marker already set, don't set twice
        }
        return this.htmlMarker + '\n' + text;
    },

    // for jquery dialogs, see toolbar.js
    replace: replaceTextarea
};

export default wysiwyg;

function replaceTextarea(textarea) {
    // TODO support jQuery object with multiple textareas
    if (!(textarea instanceof jQuery)) {
        textarea = $(textarea);
    }

    // In Firefox the browser's window is not set active after a Drag and Drop action.
    // So placeholders do not work correctly in Firefox and will be removed.
    if (CKEDITOR.env.gecko) {
        textarea.removeAttr('placeholder');
    }

    // create ID for textarea if it doesn't have one
    if (!textarea.attr('id')) {
        textarea.attr('id', createNewId('wysiwyg'));
    }

    // create new toolbar container
    var textareaHeight = Math.max(textarea.height(), 200),
        textareaWidth = (textarea.outerWidth() / textarea.parent().width()) * 100 + '%';

    // fetch ckeditor configuration
    var options = textarea.attr('data-editor'),
        extraPlugins,
        removePlugins;

    if (options) {
        options = parseOptions(options);
        extraPlugins = options.extraPlugins;
        removePlugins = options.removePlugins;
    }

    // replace textarea with editor
    CKEDITOR.replace(textarea[0], {
        allowedContent: {
            // NOTE update the dev docs when changing ACF settings!!
            // at http://docs.studip.de/develop/Entwickler/Wysiwyg
            //
            // note that changes here should also be reflected in
            // HTMLPurifier's settings!!
            a: {
                // note that external links should always have
                // class="link-extern", target="_blank" and rel="nofollow"
                // and internal links should not have any attributes except
                // for href, but this cannot be enforced here
                attributes: ['href', 'target', 'rel', 'name', 'id'],
                classes: ['link-extern', 'link-intern']
            },
            audio: {
                attributes: ['controls', '!src', 'height', 'width'],
                // only float:left and float:right should be allowed
                styles: ['float', 'height', 'width']
            },
            big: {},
            blockquote: {},
            br: {},
            caption: {},
            code: {},
            em: {},
            div: {
                classes: 'author', // needed for quotes
                // only allow left margin and horizontal text alignment to
                // be set in divs
                // - margin-left should only be settable in multiples of
                //   40 pixels
                // - text-align should only be either "center", "right" or
                //   "justify"
                // - note that maybe these two features will be removed
                //   completely in future versions
                styles: ['margin-left', 'text-align']
            },
            h1: {},
            h2: {},
            h3: {},
            h4: {},
            h5: {},
            h6: {},
            hr: {},
            img: {
                attributes: ['alt', '!src', 'height', 'width'],
                // only float:left and float:right should be allowed
                styles: ['float']
            },
            li: {},
            ol: {},
            p: {
                // - margin-left should only be settable in multiples of
                //   40 pixels
                // - text-align should only be either "center", "right" or
                //   "justify"
                styles: ['margin-left', 'text-align']
            },
            pre: {
                classes: ['usercode']
            },
            span: {
                // note that 'wiki-links' are currently set as a span due
                // to implementation difficulties, but probably this
                // might be changed in future versions
                classes: ['wiki-link', 'math-tex'],

                // note that allowed (background-)colors should be further
                // restricted
                styles: ['color', 'background-color']
            },
            strong: {},
            u: {},
            ul: {},
            s: {},
            small: {},
            sub: {},
            sup: {},
            table: {
                // note that tables should always have the class "content"
                // (it should not be optional)
                classes: 'content'
            },
            tbody: {},
            td: {
                // attributes and styles should be the same
                // as for <th>, except for 'scope' attribute
                attributes: ['colspan', 'rowspan'],
                styles: ['text-align', 'width', 'height', 'background-color']
            },
            thead: {},
            th: {
                // attributes and styles should be the same
                // as for <td>, except for 'scope' attribute
                //
                // note that allowed scope values should be restricted to
                // "col", "row" or "col row", if scope is set
                attributes: ['colspan', 'rowspan', 'scope'],
                styles: ['text-align', 'width', 'height']
            },
            tr: {},
            tt: {},
            video: {
                attributes: ['controls', '!src', 'height', 'width'],
                // only float:left and float:right should be allowed
                styles: ['float', 'height', 'width']
            }
        },
        height: textareaHeight,
        width: textareaWidth,
        skin: 'studip,' + STUDIP.ASSETS_URL + 'stylesheets/ckeditor-skin/',
        // NOTE codemirror crashes when not explicitely loaded in CKEditor 4.4.7
        extraPlugins:
            'emojione,studip-floatbar,studip-quote,studip-upload,studip-settings' +
            (extraPlugins ? ',' + extraPlugins : ''),
        removePlugins: removePlugins ? removePlugins : textarea.closest('.ui-dialog').length ? 'autogrow' : '',
        enterMode: CKEDITOR.ENTER_BR,
        mathJaxLib: STUDIP.URLHelper.getURL('assets/javascripts/mathjax/MathJax.js?config=TeX-AMS_HTML,default'),
        studipUpload_url: STUDIP.URLHelper.getURL('dispatch.php/wysiwyg/upload'),
        codemirror: {
            autoCloseTags: false,
            autoCloseBrackets: false,
            showSearchButton: false,
            showFormatButton: false,
            showCommentButton: false,
            showUncommentButton: false,
            showAutoCompleteButton: false
        },
        autoGrow_onStartup: true,

        // configure toolbar
        toolbarGroups: [
            { name: 'basicstyles', groups: ['undo', 'basicstyles', 'cleanup'] },
            { name: 'paragraph', groups: ['list', 'indent', 'blocks', 'align', 'quote'] },
            '/',
            { name: 'styles', groups: ['styles', 'colors', 'tools', 'links', 'insert'] },
            { name: 'others', groups: ['mode', 'settings'] }
        ],
        removeButtons: 'Font,FontSize',
        toolbarCanCollapse: true,
        toolbarStartupExpanded: textarea.width() > 420,

        // configure dialogs
        dialog_buttonsOrder: 'ltr',
        removeDialogTabs: 'image:Link;image:advanced;' + 'link:target;link:advanced;' + 'table:advanced',

        // convert special chars except latin ones to html entities
        entities: false,
        entities_latin: false,
        entities_processNumerical: true,

        // set WYSIWYG's menu language to the language set in Stud.IP
        defaultLanguage: 'de', // use German if user language not available
        language: String.locale, // override browser-stored preferences

        // configure list of special characters
        // NOTE 17 characters fit in one row of special characters dialog
        specialChars: [].concat(
            [
                '&Agrave;',
                '&Aacute;',
                '&Acirc;',
                '&Atilde;',
                '&Auml;',
                '&Aring;',
                '&AElig;',
                '&Egrave;',
                '&Eacute;',
                '&Ecirc;',
                '&Euml;',
                '&Igrave;',
                '&Iacute;',
                '&Iuml;',
                '&Icirc;',
                '',
                '&Yacute;',

                '&agrave;',
                '&aacute;',
                '&acirc;',
                '&atilde;',
                '&auml;',
                '&aring;',
                '&aelig;',
                '&egrave;',
                '&eacute;',
                '&ecirc;',
                '&euml;',
                '&igrave;',
                '&iacute;',
                '&iuml;',
                '&icirc;',
                '',
                '&yacute;',

                '&Ograve;',
                '&Oacute;',
                '&Ocirc;',
                '&Otilde;',
                '&Ouml;',
                '&Oslash;',
                '&OElig;',
                '&Ugrave;',
                '&Uacute;',
                '&Ucirc;',
                '&Uuml;',
                '',
                '&Ccedil;',
                '&Ntilde;',
                '&#372;',
                '',
                '&#374',

                '&ograve;',
                '&oacute;',
                '&ocirc;',
                '&otilde;',
                '&ouml;',
                '&oslash;',
                '&oelig;',
                '&ugrave;',
                '&uacute;',
                '&ucirc;',
                '&uuml;',
                '',
                '&ccedil;',
                '&ntilde;',
                '&#373',
                '',
                '&#375;',

                '&szlig;',
                '&ETH;',
                '&eth;',
                '&THORN;',
                '&thorn;',
                '',
                '',
                '`',
                '&acute;',
                '^',
                '&uml;',
                '',
                '&cedil;',
                '~',
                '&asymp;',
                '',
                '&yuml;'
            ],
            (function() {
                var greek = [];
                for (var i = 913; i <= 929; i++) {
                    // 17 uppercase characters
                    greek.push('&#' + String(i));
                }
                for (var i = 945; i <= 962; i++) {
                    // 17 lowercase characters
                    greek.push('&#' + String(i));
                }
                // NOTE character #930 is not assigned!!
                for (var i = 931; i <= 937; i++) {
                    // remaining upercase
                    greek.push('&#' + String(i));
                }
                greek.push('');
                for (var i = 963; i <= 969; i++) {
                    // remaining lowercase
                    greek.push('&#' + String(i));
                }
                greek.push('');
                return greek;
            })(),
            [
                '&ordf;',
                '&ordm;',
                '&deg;',
                '&sup1;',
                '&sup2;',
                '&sup3;',
                '&frac14;',
                '&frac12;',
                '&frac34;',
                '&lsquo;',
                '&rsquo;',
                '&ldquo;',
                '&rdquo;',
                '&laquo;',
                '&raquo;',
                '&iexcl;',
                '&iquest;',

                '@',
                '&sect;',
                '&para;',
                '&micro;',
                '[',
                ']',
                '{',
                '}',
                '|',
                '&brvbar;',
                '&ndash;',
                '&mdash;',
                '&macr;',
                '&sbquo;',
                '&#8219;',
                '&bdquo;',
                '&hellip;',

                '&euro;',
                '&cent;',
                '&pound;',
                '&yen;',
                '&curren;',
                '&copy;',
                '&reg;',
                '&trade;',

                '&not;',
                '&middot;',
                '&times;',
                '&divide;',

                '&#9658;',
                '&bull;',
                '&rarr;',
                '&rArr;',
                '&hArr;',
                '&diams;',

                '&#x00B1', // ±
                '&#x2229', // ∩ INTERSECTION
                '&#x222A', // ∪ UNION
                '&#x221E', // ∞ INFINITY
                '&#x2107', // ℇ EULER CONSTANT
                '&#x2200', // ∀ FOR ALL
                '&#x2201', // ∁ COMPLEMENT
                '&#x2202', // ∂ PARTIAL DIFFERENTIAL
                '&#x2203', // ∃ THERE EXISTS
                '&#x2204', // ∄ THERE DOES NOT EXIST
                '&#x2205', // ∅ EMPTY SET
                '&#x2206', // ∆ INCREMENT
                '&#x2207', // ∇ NABLA
                '&#x2282', // ⊂ SUBSET OF
                '&#x2283', // ⊃ SUPERSET OF
                '&#x2284', // ⊄ NOT A SUBSET OF
                '&#x2286', // ⊆ SUBSET OF OR EQUAL TO
                '&#x2287', // ⊇ SUPERSET OF OR EQUAL TO
                '&#x2208', // ∈ ELEMENT OF
                '&#x2209', // ∉ NOT AN ELEMENT OF
                '&#x2227', // ∧ LOGICAL AND
                '&#x2228', // ∨ LOGICAL OR
                '&#x2264', // ≤ LESS-THAN OR EQUAL TO
                '&#x2265', // ≥ GREATER-THAN OR EQUAL TO
                '&#x220E', // ∎ END OF PROOF
                '&#x220F', // ∏ N-ARY PRODUCT
                '&#x2211', // ∑ N-ARY SUMMATION
                '&#x221A', // √ SQUARE ROOT
                '&#x222B', // ∫ INTEGRAL
                '&#x2234', // ∴ THEREFORE
                '&#x2235', // ∵ BECAUSE
                '&#x2260', // ≠ NOT EQUAL TO
                '&#x2262', // ≢ NOT IDENTICAL TO
                '&#x2263', // ≣ STRICTLY EQUIVALENT TO
                '&#x22A2', // ⊢ RIGHT TACK
                '&#x22A3', // ⊣ LEFT TACK
                '&#x22A4', // ⊤ DOWN TACK
                '&#x22A5', // ⊥ UP TACK
                '&#x22A7', // ⊧ MODELS
                '&#x22A8', // ⊨ TRUE
                '&#x22AC', // ⊬ DOES NOT PROVE
                '&#x22AD', // ⊭ NOT TRUE
                '&#x22EE', // ⋮ VERTICAL ELLIPSIS
                '&#x22EF', // ⋯ MIDLINE HORIZONTAL ELLIPSIS
                '&#x29FC', // ⧼ LEFT-POINTING CURVED ANGLE BRACKET
                '&#x29FD', // ⧽ RIGHT-POINTING CURVED ANGLE BRACKET
                '&#x207F', // ⁿ SUPERSCRIPT LATIN SMALL LETTER N
                '&#x2295', // ⊕ CIRCLED PLUS
                '&#x2297', // ⊗ CIRCLED TIMES
                '&#x2299' // ⊙ CIRCLED DOT OPERATOR
            ]
        ),
        on: { pluginsLoaded: onPluginsLoaded },
        title: false
    });

    CKEDITOR.on('instanceReady', function(event) {
        var editor = event.editor,
            $textarea = $(editor.element.$);

        // auto-resize editor area in source view mode, and keep focus!
        editor.on('mode', function(event) {
            var editor = event.editor;
            if (editor.mode === 'source') {
                $(editor.container.$)
                    .find('.cke_source')
                    .focus();
            } else {
                editor.focus();
            }
        });

	// fix for not pasting text from clipboard twice on firefox in a dialog
	if (CKEDITOR.env.gecko && $textarea.closest('.ui-dialog').length) {
            $(editor.container.$).on('paste', function(event) {
                event.preventDefault();
            });
	}

        // clean up HTML edited in source mode before submit
        var form = $textarea.closest('form');
        form.submit(function(event) {
            // make sure HTML marker is always set, in
            // case contents are cut-off by the backend
            editor.setData(wysiwyg.markAsHtml(editor.getData()));
            editor.updateElement(); // update textarea, in case it's accessed by other JS code
        });

        // update textarea on editor blur
        editor.on('blur', function(event) {
            event.editor.updateElement();
        });
        $(editor.container.$).on('blur', '.CodeMirror', function(event) {
            editor.updateElement(); // also update in source mode
        });

        // blurDelay = 0 is an ugly hack to be faster than Stud.IP
        // forum's save function; might produce "strange" behaviour
        CKEDITOR.focusManager._.blurDelay = 0;

        // display "focused"-effect when editor area is focused
        editor.on('focus', function(event) {
            event.editor.container.addClass('cke_chrome_focused');
        });
        editor.on('blur', function(event) {
            event.editor.container.removeClass('cke_chrome_focused');
        });

        // keep the editor focused when a toolbar item gets selected
        editor.on('blur', function(event) {
            var toolbarContainer = $('#' + event.editor.config.sharedSpaces.top);
            if (toolbarContainer.has(':focus').length > 0) {
                event.editor.focus();
            }
        });

        // Trigger load event for the editor event. Uses the underlying
        // textarea element to ensure that the event will be catchable by
        // jQuery.
        $textarea.trigger('load.wysiwyg');

        // focus the editor if requested
        if ($textarea.is('[autofocus]')) {
            editor.focus();
        }
    });
}

// editor events
function onPluginsLoaded(event) {
    // tell editor to always remove html comments
    event.editor.dataProcessor.htmlFilter.addRules({
        comment: function(element) {
            if (!wysiwyg.hasHtmlMarker(decodeURIComponent(element).substring(18))) {
                return false;
            }
        }
    });
}

// create an unused id
function createNewId(prefix) {
    var i = 0;
    while ($('#' + prefix + i).length > 0) {
        i++;
    }
    return prefix + i;
}
