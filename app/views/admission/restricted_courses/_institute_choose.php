<form action="?" method="post" name="institute_choose" class="default">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= _('Anmeldesets auflisten') ?></legend>

        <label>
            <?=_("Einrichtung:")?>
            <select name="choose_institut_id" class="nested-select">
            <? while (list($institut_id,$institute) = each($my_inst)) : ?>
                <option value="<?= $institut_id?>" <?=($current_institut_id == $institut_id ? 'selected' : '')?> class="<?= $institute['is_fak'] ? 'nested-item-header' : 'nested-item' ?>">
                    <?= htmlReady(my_substr($institute["name"] . ' (' . $institute["num_sem"] . ')',0,100));?>
                </option>
                <? if ($institute["is_fak"] == 'all') : ?>
                    <? $num_inst = $institute["num_inst"]; for ($i = 0; $i < $num_inst; ++$i) : ?>
                        <? list($institut_id,$institute) = each($my_inst);?>
                        <option value="<?= $institut_id?>" <?=($current_institut_id == $institut_id ? 'selected' : '')?> class="nested-item">
                            <?= htmlReady(my_substr($institute["name"] . ' (' . $institute["num_sem"] . ')',0,100));?>
                        </option>
                    <? endfor ?>
                <? endif ?>
            <? endwhile ?>
            </select>
        </label>

        <label>
            <?=_("Präfix des Veranstaltungsnamens / Nummer:")?>
            <input type="text" name="sem_name_prefix" value="<?=htmlReady($sem_name_prefix)?>" size="40">
        </label>

        <label>
            <?=_("Veranstaltungen aus diesem Semester:")?>
            <?=SemesterData::GetSemesterSelector(['name'=>'select_semester_id'], $current_semester_id, 'semester_id', false)?>
        </label>
    </fieldset>

    <footer>
        <?= Studip\Button::create(_('Auswählen'), 'choose_institut', ['title' => _("Einrichtung auswählen")]) ?>
    </footer>
</form>
