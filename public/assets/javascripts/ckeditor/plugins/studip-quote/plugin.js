// widget for handling studip-style quoting with author name
(function studipQuotePlugin(CKEDITOR) {
    CKEDITOR.plugins.add('studip-quote', {
        icons: 'blockquote,splitquote,removequote',
        hidpi: true,
        init: initPlugin
    });

    function initPlugin(editor) {        
        editor.addCommand('insertStudipQuote', {
            exec: insertStudipQuote
        });

        editor.addCommand('splitQuote', {
            exec: splitStudipQuote
        });

        editor.addCommand('removeQuote', {
            exec: removeStudipQuote
        });

        editor.ui.addButton('blockquote', {
            label: 'Zitat einfügen'.toLocaleString(),
            command: 'insertStudipQuote',
            toolbar: 'quote'
        });

        editor.ui.addButton('SplitQuote', {
            label: 'Zitat teilen'.toLocaleString(),
            command: 'splitQuote',
            toolbar: 'quote'
        });

        editor.ui.addButton('RemoveQuote', {
            label: 'Zitat löschen'.toLocaleString(),
            command: 'removeQuote',
            toolbar: 'quote'
        });

        editor.setKeystroke(CKEDITOR.CTRL + CKEDITOR.SHIFT + 13, 'splitQuote'); // CTRL+SHIFT+Return
    }

    function insertStudipQuote(editor) {
        // If quoting is changed update these functions:
        // - StudipFormat::markupQuote
        //   lib/classes/StudipFormat.php
        // - quotes_encode lib/visual.inc.php
        // - STUDIP.Forum.citeEntry > quote
        //   public/plugins_packages/core/Forum/javascript/forum.js
        // - studipQuotePlugin > insertStudipQuote
        //   public/assets/javascripts/ckeditor/plugins/studip-quote/plugin.js

        var writtenBy = '%s hat geschrieben:'.toLocaleString();

        // TODO generate HTML tags with JS/jQuery functions
        editor.insertHtml(
            '<blockquote><div class="author">'
            + writtenBy.replace('%s', '"Name"')
            + '</div><p>&nbsp</p></blockquote><p>&nbsp;</p>'
        );
    }

    function splitStudipQuote(editor) {
        // is the cursor position within a blockquote?
        var blockquote = editor.elementPath().contains('blockquote', true, false);
        if (blockquote !== null) {
            var pElement = CKEDITOR.dom.element.createFromHtml('<p></p>');
            editor.insertElement(pElement);
            pElement.breakParent(blockquote);
            var range = editor.createRange();
            range.moveToElementEditablePosition(pElement);
            editor.getSelection().selectRanges([range]);
        }
    }

    function removeStudipQuote(editor) {
        // is the cursor position within a blockquote?
        var blockquote = editor.elementPath().contains('blockquote', true, false);
        if (blockquote !== null) {
            blockquote.remove(true);
        }
    }
})(CKEDITOR);
