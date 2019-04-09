<div id="activityEdit">
    <form id="configure_activity" action="<?= PluginEngine::getURL($plugin, [], 'save') ?>" method="post" class="default" data-dialog>
        <fieldset>
            <legend><?= _("Anzuzeigende Bereiche:") ?></legend>

            <? foreach ($modules as $context => $provider) : ?>
            <fieldset>
                <legend><?= htmlReady($context_translations[$context]) ?></legend>
                <? foreach ($provider as $prv_id => $prv_name) : ?>
                <label>
                    <input type="checkbox" name="provider[<?= $context ?>][]" value="<?= htmlReady($prv_id) ?>"
                        <?= empty($config) || (is_array($config[$context]) && in_array($prv_id, $config[$context])) ? 'checked' : ''?>>
                    <?= htmlReady($prv_name) ?>
                </label>
                <? endforeach ?>
            </fieldset>
            <? endforeach ?>
        </fieldset>
        <footer data-dialog-button>
            <?= Studip\Button::createAccept(_('Speichern')) ?>
            <?= Studip\Button::createCancel(_('Abbrechen'), URLHelper::getLink('dispatch.php/start')) ?>
        </footer>
    </form>
</div>
