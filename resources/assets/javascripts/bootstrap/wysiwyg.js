STUDIP.domReady(() => {
    if (STUDIP.editor_enabled) {
        // replace areas visible on page load
        replaceVisibleTextareas();

        // replace areas that are created or shown after page load
        // remove editors that become hidden after page load
        // show, hide and create do not raise an event, use interval timer
        setInterval(replaceVisibleTextareas, 300);
    }

    // when attaching to hidden textareas, or textareas who's parents are
    // hidden, the editor does not function properly; therefore attach to
    // visible textareas only
    function replaceVisibleTextareas() {
        $('textarea.wysiwyg').each(function() {
            var editor = CKEDITOR.dom.element.get(this).getEditor();
            if (!editor && $(this).is(':visible')) {
                STUDIP.wysiwyg.replace(this);
            } else if (editor && editor.container && $(editor.container.$).is(':hidden')) {
                editor.destroy(true);
            }
        });
    }

    // customize existing dialog windows
    CKEDITOR.on('dialogDefinition', function(ev) {
        var dialogName = ev.data.name,
            dialogDefinition = ev.data.definition;

        if (dialogName == 'table') {
            var infoTab = dialogDefinition.getContents('info');
            infoTab.get('txtBorder')['default'] = '';
            infoTab.get('txtWidth')['default'] = '';
            infoTab.get('txtCellSpace')['default'] = '';
            infoTab.get('txtCellPad')['default'] = '';

            var advancedTab = dialogDefinition.getContents('advanced');
            advancedTab.get('advCSSClasses')['default'] = 'content';
        }
    });
});

// Hotfix for Dialogs

$.widget( "ui.dialog", $.ui.dialog, {

  // jQuery UI v1.11+ fix to accommodate CKEditor (and other iframed content) inside a dialog
  // @see http://bugs.jqueryui.com/ticket/9087
  // @see http://dev.ckeditor.com/ticket/10269

  _allowInteraction: function( event ) {
    return this._super( event ) ||

      // addresses general interaction issues with iframes inside a dialog
      event.target.ownerDocument !== this.document[ 0 ] ||

      // addresses interaction issues with CKEditor's dialog windows and iframe-based dropdowns in IE
      !!$( event.target ).closest( ".cke_dialog, .cke_dialog_background_cover, .cke" ).length;
  }
});
