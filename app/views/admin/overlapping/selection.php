<form method="post" class="default collapsable mvv-ovl-selection" action="<?= $controller->url_for('admin/overlapping/check') ?>">
    <fieldset>
        <legend>
            <?= _('Auswahl') ?>
        </legend>

        <label for="base-version-select">
            <?= _('Studiengangteil') ?>
        </label>
        <select id="base-version-select" class="nested-select" name="base_version">
        <? if ($base_version) : ?>
            <option value="<?= $base_version->id ?>" selected><?= htmlReady($base_version->getDisplayName()) ?></option>
        <? endif; ?>
        </select>

        <label for="comp-versions-select">
            <?= _('Vergleichs-Studiengangteile') ?>
        </label>
        <select id="comp-versions-select" class="nested-select" name="comp_versions[]" multiple>
        <? if (count($comp_versions)) : ?>
            <? foreach($comp_versions as $comp_version) : ?>
                <option value="<?= $comp_version->id ?>" selected><?= htmlReady($comp_version->getDisplayName()) ?></option>
            <? endforeach; ?>
        <? endif; ?>
        </select>
        
        <label for="fachsem-select">
            <?= _('Fachsemester') ?>
        </label>
        <select id="fachsem-select" class="nested-select" name="fachsems[]" multiple>
            <? foreach (range(1, 6) as $fsem) : ?>
                <option value="<?= $fsem ?>"<?= in_array($fsem, (array) $fachsems) ? ' selected' : '' ?>>
                <?= $fsem . ModuleManagementModel::getLocaleOrdinalNumberSuffix($fsem) . ' ' . _('Fachsemester') ?>
                </option>
            <? endforeach; ?>
        </select>

        <label for="semtype-select">
            <?= _('Veranstaltungstyp-Filter') ?>
        </label>
        <select id="semtype-select" class="nested-select" name="semtypes[]" multiple>
            <? foreach ($GLOBALS['SEM_CLASS'] as $class_id => $class) : ?>
                <? if ($class['studygroup_mode']) : continue;
                endif; ?>
                <optgroup class="nested-item-header" label="<?= htmlReady($class['name']) ?>">
                    <? foreach ($class->getSemTypes() as $id => $type) : ?>
                        <option class="nested-item nested-item-level-2"
                                value="<?= $id ?>"<?= in_array($id, (array) $semtypes) ? ' selected' : '' ?>>
                            <?= htmlReady($type['name']) ?>
                        </option>
                    <? endforeach; ?>
                </optgroup>
            <? endforeach; ?>
        </select>

        <label>
            <input type="checkbox"
                   name="show_hidden"
                   value="1" <?= $_SESSION['MVV_OVL_HIDDEN'] ? ' checked' : '' ?>>
            <?= _('ausgeblendete Veranstaltungen anzeigen') ?>
        </label>
    </fieldset>
    <footer>
        <?= \Studip\Button::createAccept(_('Vergleichen'), 'compare') ?>
        <?= \Studip\Button::createCancel(_('Zurücksetzen'), 'index', ['formaction' => $controller->url_for('/reset')]) ?>
    </footer>
</form>
