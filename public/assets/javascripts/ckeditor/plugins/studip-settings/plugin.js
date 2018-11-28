CKEDITOR.plugins.add('studip-settings', {
    icons: 'settings',
    hidipi: true,
    lang: 'de,en',
    init: function (editor) {
        CKEDITOR.dialog.add(
            'settingsDialog',
            this.path + 'dialogs/settings.js'
        );
        editor.addCommand(
            'settings',
            new CKEDITOR.dialogCommand('settingsDialog')
        );
        editor.ui.addButton('settings', {
            label: editor.lang['studip-settings'].buttonLabel,
            command: 'settings',
            toolbar: 'settings'
        });
    }
});
