<form class="default" action="<?= $controller->url_for('globalsearch/saveconfig') ?>" method="post">
    <section>
        <label>
            <span class="required">
                <?= _('Wieviele Ergebnisse pro Kategorie sollen in der Schnellsuche angezeigt werden?') ?>
            </span>
            <input type="number" name="entries_per_type" size="5"
                   value="<?= intval(Config::get()->GLOBALSEARCH_MAX_RESULT_OF_TYPE) ?>" required>
        </label>
    </section>
    <br>
    <section>
        <label>
            <?= _('Auf welche Art sollen die Suchergebnisse aus der Datenbank gelesen werden?') ?>
            <select name="async_queries" size="1">
                <?php if (in_array('mysqli', get_loaded_extensions())) : ?>
                    <option value="1"<?= Config::get()->GLOBALSEARCH_ASYNC_QUERIES ? ' selected' : '' ?>>
                        <?= _('Asynchron, via mysqli') ?>
                    </option>
                <?php endif ?>
                <option value="0"<?= Config::get()->GLOBALSEARCH_ASYNC_QUERIES ? '' : ' selected' ?>>
                    <?= _('Synchron, via PDO') ?>
                </option>
            </select>
        </label>
    </section>
    <br>
    <section>
        <table class="default sortable-table" id="globalsearch-modules">
            <caption>
                <?= _('In welcher Reihenfolge sollen Suchergebnisse erscheinen?') ?>
            </caption>
            <colgroup>
                <col width="10">
                <col>
                <col width="50">
                <col width="50">
            </colgroup>
            <thead>
                <tr>
                    <th></th>
                    <th><?= _('Kategorie') ?></th>
                    <th><?= _('Aktiv?') ?></th>
                    <th><?= _('Volltext?') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($modules as $module) : ?>
                    <tr>
                        <td class="dragHandle"></td>
                        <td>
                            <label for="active[<?= htmlReady(get_class($module)) ?>]">
                                <?= htmlReady($module->getName()) ?>
                            </label>
                            <input type="hidden" name="modules[<?= get_class($module) ?>][class]"
                                   value="<?= get_class($module) ?>">
                        </td>
                        <td>
                            <input type="checkbox" id="active[<?= htmlReady(get_class($module)) ?>]"
                                   name="modules[<?= get_class($module) ?>][active]" value="1"
                                <?= $config[get_class($module)]['active'] ? ' checked' : ''?>>
                        </td>
                        <td>
                            <?php if (is_a($module, 'GlobalSearchFulltext')) : ?>
                                <input type="checkbox" name="modules[<?= get_class($module) ?>][fulltext]"
                                       value="1"<?= $config[get_class($module)]['fulltext'] ? ' checked' : ''?>>
                            <?php endif ?>
                        </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </section>
    <?= CSRFProtection::tokenTag() ?>
    <footer data-dialog-button>
        <?= \Studip\Button::createAccept(_('Speichern'), 'submit')?>
    </footer>
</form>

<script language="JavaScript" type="text/javascript">
    //<!--
    jQuery('#globalsearch-modules tbody').sortable();
    //-->
</script>