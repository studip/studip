<? if ($selected_status || $selected_kategorie || $selected_abschluss || $selected_fachbereich || $selected_zuordnung || $selected_institut || $selected_semester != $default_semester) : ?>
<div style="width: 100%; text-align: right;">
    <a href="<?= $action_reset ?>">
        <?= Icon::create('refresh', 'clickable', ['title' => _('Filter zurücksetzen')])->asImg(); ?>
        <?= _('Zurücksetzen') ?>
    </a>
</div>
<? endif; ?>
<form id="index_filter" action="<?= $action ?>" method="post">
    <? if (isset($semester)) : ?>
    <label>
        <?= $semester_caption ?: _('Semester:') ?><br>
        <select name="semester_filter" class="sidebar-selectlist submit-upon-select">
            <option value="all"<?= (!$selected_semester ? ' selected' : '') ?>><?= _('Alle Semester') ?></option>
            <? foreach ($semester as $sem) : ?>
            <option value="<?= $sem['semester_id'] ?>"<?= ($sem['semester_id'] == $selected_semester ? ' selected' : '') ?>><?= htmlReady($sem['name']) ?></option>
            <? endforeach; ?>
        </select>
    </label>
    <? endif; ?>
    <? if (isset($zuordnungen)) : ?>
    <label>
        <?= _('Zugeordnet zu Objekten:') ?>
        <select name="zuordnung_filter" class="sidebar-selectlist submit-upon-select">
            <option value=""><?= _('Alle') ?></option>
            <? foreach ($zuordnungen as $object_type => $zuordnung) : ?>
            <option value="<?= $object_type ?>"
                <?= ($object_type == $selected_zuordnung ? ' selected' : '') ?>><?= htmlReady($object_type::getClassDisplayName()) ?></option>
            <? endforeach; ?>
        </select>
    </label>
    <? endif; ?>
    <? if (isset($status)) : ?>
    <label>
        <?= _('Status:') ?><br>
        <select name="status_filter" class="sidebar-selectlist submit-upon-select">
            <option value=""><?= _('Alle') ?></option>
            <? foreach ($status_array as $key => $stat) : ?>
            <? if ($status[$key]['count_objects']) : ?>
            <option value="<?= $key ?>"
                <?= ($key == $selected_status ? ' selected' : '') ?>><?= htmlReady($stat['name']) . ' (' . ($status[$key] ? $status[$key]['count_objects'] : '0') . ')' ?></option>
            <? endif; ?>
            <? endforeach; ?>
            <? if ($status['__undefined__']) : ?>
                <option value="__undefined__"<?= $selected_status == '__undefined__' ? ' selected' : '' ?>><?= _('nicht angegeben')  . ' (' . ($stat['count_objects'] ?: '0') . ')' ?></option>
            <? endif; ?>
        </select>
    </label>
    <? endif; ?>
    <? if (isset($kategorien)) : ?>
    <label>
        <?= _('Kategorie:') ?><br>
        <select name="kategorie_filter" class="sidebar-selectlist submit-upon-select">
            <option value=""><?= _('Alle') ?></option>
            <? foreach ($kategorien as $kategorie) : ?>
            <option value="<?= $kategorie->getId() ?>"
                <?= ($kategorie->getId() == $selected_kategorie || (!isset($abschluesse) || $abschluesse[$selected_abschluss]->kategorie_id == $kategorie->getId()) ? ' selected' : '') ?>><?= htmlReady($kategorie->name) . ' (' . $kategorie->count_objects . ')'  ?></option>
            <? endforeach; ?>
        </select>
    </label>
    <? endif; ?>
    <? if (isset($abschluesse)) : ?>
    <label>
        <?= _('Abschluss:') ?><br>
        <select name="abschluss_filter" class="sidebar-selectlist submit-upon-select">
            <option value=""><?= _('Alle') ?></option>
            <? foreach ($abschluesse as $abschluss) : ?>
            <option value="<?= $abschluss->getId() ?>"<?= ($abschluss->getId() == $selected_abschluss ? ' selected' : '') ?>><?= htmlReady($abschluss->name) . ' (' . $abschluss->count_objects . ')' ?></option>
            <? endforeach; ?>
        </select>
    </label>
    <? endif; ?>
    <? if (isset($institute)) : ?>
        <? $perm_institutes = MvvPerm::getOwnInstitutes() ?>
        <? if ($perm_institutes !== false) : ?>
        <label>
            <?= _('Verantw. Einrichtung:') ?><br>
            <select name="institut_filter" class="sidebar-selectlist nested-select submit-upon-select">
                <option value=""><?= _('Alle') ?></option>
                <? $fak = '' ?>
                <? foreach ($institute as $institut) : ?>
                    <?
                    if (count($perm_institutes) == 0
                            || in_array($institut->getId(), $perm_institutes)) {
                            echo '<option value="' . $institut->getId()
                                . ($institut->getId() == $selected_institut ?
                                    '" selected' : '"')
                                . ' class="nested-item">'
                                . htmlReady($institut->name
                                . ' (' . $institut->count_objects . ')')
                                . '</option>';
                    }
                    ?>
                <? endforeach; ?>
            </select>
        </label>
        <? endif ?>
    <? endif; ?>
    <? if (isset($fachbereiche)) : ?>
        <? $perm_institutes = MvvPerm::getOwnInstitutes() ?>
        <? if ($perm_institutes !== false) : ?>
        <label>
            <?= $fachbereich_caption ?: _('Fachbereiche:') ?><br>
            <select name="fachbereich_filter" class="sidebar-selectlist nested-select institute-list submit-upon-select">
                <option value=""><?= _('Alle') ?></option>
                <? foreach ($fachbereiche as $fachbereich) : ?>
                    <? if (count($perm_institutes) == 0
                            || in_array($fachbereich->getId(), $perm_institutes)) : ?>
                    <option class="nested-item" value="<?= $fachbereich->getId() ?>"<?= ($fachbereich->getId() == $selected_fachbereich ? ' selected' : '') ?>><?= htmlReady($fachbereich->getDisplayName()) . ' (' . $fachbereich->count_objects . ')' ?></option>
                    <? endif; ?>
                <? endforeach; ?>
            </select>
        </label>
        <? endif; ?>
    <? endif; ?>
</form>

