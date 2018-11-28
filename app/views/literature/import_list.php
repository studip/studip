<? use Studip\Button, Studip\LinkButton; ?>
<? if (count($plugin)) : ?>
    <form enctype="multipart/form-data" class="default"
          action="<?= URLHelper::getLink('dispatch.php/literature/edit_list?_range_id=' . $return_range) ?>"
          method="post">
        <?= CSRFProtection::tokenTag() ?>
        <fieldset>
            <legend><?= _('Datei hochladen') ?></legend>
            <?= mb_strlen($plugin["description"]) > 0 ? Icon::create('info-circle', 'inactive')->asImg(16) : '' ?>
            <?= formatReady($plugin["description"]) ?><br>
            <input type="hidden" name="cmd" value="import_lit_list">
            <input type="hidden" name="plugin_name" value="<?= htmlReady($plugin['name']) ?>">
            <label>
                <?= _('Wählen Sie mit <b>Durchsuchen</b> eine Datei von Ihrer Festplatte aus.') ?><br>
                <input name="xmlfile" type="file">
            </label>
        </fieldset>
        <footer data-dialog-button>
            <?= Button::createAccept(_('Importieren')) ?>
        </footer>
    </form>
<? else : ?>
    <form class="default"
          action="<?= URLHelper::getLink('dispatch.php/literature/import_list?return_range=' . $return_range) ?>"
          method="post" data-dialog>
        <?= CSRFProtection::tokenTag() ?>
        <fieldset>
            <legend><?= _('Format wählen') ?></legend>
            <label for="plugin_name">
                <?= _('Bitte wählen Sie eine Literaturverwaltung aus:'); ?>
                <select name="plugin_name" size="1" class="submit-upon-select">
                    <? foreach ($GLOBALS['LIT_IMPORT_PLUGINS'] as $p) : ?>
                        <option value="<?= htmlReady($p["name"]) ?>">
                            <?= htmlReady($p["visual_name"]) ?>
                        </option>
                    <? endforeach; ?>
                </select>
            </label>
            <input type="hidden" name="cmd" value="">
        </fieldset>
        <footer data-dialog-button>
            <?= Button::createAccept(_('Auswählen'), ['id' => 'lit_choose_plugin']) ?>
        </footer>
    </form>
<? endif; ?>
