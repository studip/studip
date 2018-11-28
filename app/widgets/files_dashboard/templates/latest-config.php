<?
$options = $widget->getOptions();
?>
<form id="files_dashboard_latest_configure" action="<?= $widget->url_for('saveConfiguration') ?>" method="post" class="default" data-dialog>
    <fieldset>
        <legend><?= _('Wieviele Dateien sollen angezeigt werden?') ?></legend>

        <label>
            <input type="number"
                   min="1"
                   max="10"
                   name="limit"
                   value="<?= $options['limit'] ?: \Widgets\FilesDashboard\LatestFilesWidget::DEFAULT_LIMIT ?>">
        </label>
    </fieldset>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern')) ?>
        <?= Studip\Button::createCancel(_('Abbrechen'), URLHelper::getLink('dispatch.php/files_dashboard')) ?>
    </footer>
</form>
