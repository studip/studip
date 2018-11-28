<div id="quickSelectionEdit">
    <form id="configure_quickselection" action="<?= PluginEngine::getURL($plugin, array(), 'save') ?>" method="post" class="default" data-dialog>
        <fieldset>
            <legend><?= _("Inhalte des Schnellzugriff-Widget:") ?></legend>
            <fieldset>
            <? foreach ($links as $key=>$nav) : ?>
                <label>
                    <input type="checkbox" name="add_removes[]" value="<?= htmlReady($key) ?>" <?= empty($config) || in_array($key, $config) ? 'checked' : ''?>>
                    <?= htmlReady($nav->getTitle()) ?>
                </label>
            <? endforeach ?>
            </fieldset>
        </fieldset>
        <footer data-dialog-button>
            <?= Studip\Button::createAccept(_('Speichern')) ?>
            <?= Studip\Button::createCancel(_('Abbrechen'), URLHelper::getLink('dispatch.php/start')) ?>
        </footer>
    </form>
</div>
