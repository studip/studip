<form class="default" method="post"
    action="<?= URLHelper::getLink('dispatch.php/admin/courses/sidebar'); ?>" >
    <input type="hidden" name="updateConfig" value="1">
    <fieldset>
        <legend><?= _('Standardfelder') ?></legend>
        <label>
            <input name="searchActive" type="checkbox" value="1"
                <?= ($userSelectedElements['search']) ? 'checked="checked"' : '' ?>
                >
            <?= _('Freie Suche'); ?>
        </label>

        <label>
            <input name="instituteActive" type="checkbox" value="1"
                <?= ($userSelectedElements['institute']) ? 'checked="checked"' : '' ?>
                >
            <?= _('Einrichtung'); ?>
        </label>
        <label>
            <input name="semesterActive" type="checkbox" value="1"
                <?= ($userSelectedElements['semester']) ? 'checked="checked"' : '' ?>
                >
            <?= _('Semester'); ?>
        </label>
        <label>
            <input name="courseTypeActive" type="checkbox" value="1"
                <?= ($userSelectedElements['courseType']) ? 'checked="checked"' : '' ?>
                >
            <?= _('Veranstaltungstypfilter'); ?>
        </label>
        <label>
            <input name="teacherActive" type="checkbox" value="1"
                <?= ($userSelectedElements['teacher']) ? 'checked="checked"' : '' ?>
                >
            <?= _('Dozent'); ?>
        </label>
        <label>
            <input name="viewFilterActive" type="checkbox" value="1"
                <?= ($userSelectedElements['viewFilter']) ? 'checked="checked"' : '' ?>
                >
            <?= _('Darstellungsfilter'); ?>
        </label>
    </fieldset>
    <? if ($datafields): ?>
    <fieldset>
        <legend><?= _('Datenfelder') ?></legend>
        <? foreach ($datafields as $datafield): ?>
        <label>
            <input name="activeDatafields[]" type="checkbox" value="<?= htmlReady($datafield->id) ?>"
                <? if ($userSelectedElements['datafields']) : ?>
                <?= in_array($datafield->id, $userSelectedElements['datafields']) ? 'checked="checked"' : '' ?>
                <? endif ?>
                >
            <?= $datafield->name ?>
        </label>
        <? endforeach ?>
    </fieldset>
    <? endif ?>
    <div data-dialog-button>
        <?= \Studip\Button::create(_('Speichern')); ?>
    </div>
</form>
