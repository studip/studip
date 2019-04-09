<form action="?" method="post" name="institute_choose" class="default">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend><?= _('Anmeldesets auflisten') ?></legend>

        <label>
            <?=_("Einrichtung:")?>
            <select name="choose_institut_id" class="nested-select">
                <? while (list($institut_id,$institute) = each($myInstitutes)) : ?>
                <option value="<?= $institut_id ?>" <?=($current_institut_id == $institut_id ? 'selected' : '')?> class="<?= $institute['is_fak'] ? 'nested-item-header' : 'nested-item' ?>">
                    <?= htmlReady(my_substr($institute["name"] . ' (' . $institute["num_sets"] . ')',0,100));?>
                </option>
                <? if ($institute['is_fak'] === 'all') : ?>
                    <? $num_inst = $institute['num_inst']; for ($i = 0; $i < $num_inst; ++$i) : ?>
                        <? list($institut_id,$institute) = each($myInstitutes);?>
                        <option value="<?= $institut_id?>" <?=($current_institut_id == $institut_id ? 'selected' : '')?> class="nested-item">
                            <?= htmlReady(my_substr($institute["name"] . ' (' . $institute["num_sets"] . ')',0,100));?>
                        </option>
                    <? endfor ?>
                <? endif ?>
            <? endwhile ?>
            </select>
        </label>

        <label>
            <?=_("Präfix des Namens:")?>
            <input type="text" name="set_name_prefix" value="<?=htmlReady($set_name_prefix)?>" size="40">
        </label>

        <section>
            <?=_("Enthaltene Regeln:")?>
            <div class="hidden-no-js check_actions">
                (<?= _('markieren') ?>:
                <a onclick="STUDIP.Admission.checkUncheckAll('choose_rule_type', 'check')">
                    <?= _('alle') ?>
                </a>
                |
                <a onclick="STUDIP.Admission.checkUncheckAll('choose_rule_type', 'uncheck')">
                    <?= _('keine') ?>
                </a>
                |
                <a onclick="STUDIP.Admission.checkUncheckAll('choose_rule_type', 'invert')">
                    <?= _('Auswahl umkehren') ?>
                </a>)
            </div>
        </section>

        <div>
            <? foreach ($ruleTypes as $type => $detail) : ?>
            <label class="col-2">
                <input type="checkbox" name="choose_rule_type[<?= $type?>]" <?=(isset($current_rule_types[$type]) ? 'checked' : '')?> value="1">
                <?= htmlReady($detail['name']);?>
            </label>
            <? endforeach; ?>
        </div>

        <label>
            <?=_("Zugewiesene Veranstaltungen aus diesem Semester:")?>
            <?=SemesterData::GetSemesterSelector(['name'=>'select_semester_id'], $current_semester_id, 'semester_id', true)?>
        </label>
    </fieldset>

    <footer>
        <?= Studip\Button::create(_('Auswählen'), 'choose_institut', ['title' => _("Einrichtung auswählen")]) ?>
    </footer>
</form>
<br>
