CKEDITOR.dialog.add('wikiDialog', function (editor) {
    // studip wiki link specification
    // * allowed characters: a-z.-:()_§/@# äöüß
    // * enclose in double-brackets: [[wiki link]]
    // * leading or trailing whitespace is allowed!!
    // * extended: [[wiki link| displayed text]]
    // * displayed text characters can be anything but ]

    // dialog
    var lang = editor.lang['studip-wiki'];
    return {
        title: lang.dialogTitle,
        minWidth: 400,
        minHeight: 200,
        contents: [{
            id: 'tab-link',
            label: 'Stud.IP-Wiki Link',
            elements: [{
                type: 'text',
                id: 'wikipage',
                label: lang.pageNameLabel,
                // TODO regex encoding is not working correctly
                // ==> german umlauts cannot be entered using the wiki widget
                // ==> users have to manually enter wikilinks with umlauts atm.
                validate: CKEDITOR.dialog.validate.regex(
                    /^[\w\.\-\:\(\)§\/@# ÄÖÜäöüß]+$/i,
                    lang.invalidPageName
                ),
                setup: function(widget) {
                    this.setValue(widget.data.link);
                },
                commit: function(widget) {
                    widget.setData('link', this.getValue().trim());
                }
            }, {
                type: 'text',
                id: 'usertext',
                label: lang.displayTextLabel,
                validate: CKEDITOR.dialog.validate.regex(
                    /^[^\]]*$/i,
                    lang.invalidDisplayText
                ),
                setup: function(widget) {
                    this.setValue(widget.data.text);
                },
                commit: function(widget) {
                    widget.setData('text', this.getValue().trim());
                }
            }]
        }]
    };
});
