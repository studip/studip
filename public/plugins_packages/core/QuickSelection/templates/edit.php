<div id="quickSelectionEdit">
    <form id="configure_quickselection" action="<?= PluginEngine::getLink($plugin, [], 'save') ?>" method="post" class="default" data-dialog>
        <fieldset>
            <legend><?= _("Inhalte des Schnellzugriff-Widget:") ?></legend>
            <fieldset>
            <? foreach ($links as $key=>$nav) : ?>
                <label>
                    <!-- values which are not in $config are displayed checked,
                    but checked values will be returned via add_removes[]  and be stored with a 'deactivated' value-->
                    <input type="checkbox" name="add_removes[]" value="<?= htmlReady($key) ?>"
                        <?= (empty($config) || !isset($config[$key]) || $config[$key] !== 'deactivated') ? 'checked' : ''?>>
                    <?= htmlReady($nav->getTitle()) ?>
                </label>
            <? endforeach ?>
            </fieldset>
        </fieldset>
        <footer data-dialog-button>
            <?= Studip\Button::createAccept(_('Speichern')) ?>
            <?= Studip\Button::createCancel(_('Abbrechen'), URLHelper::getURL('dispatch.php/start')) ?>
        </footer>
    </form>
</div>
