<form class="default"  method="post" id="questionnaire-assign-form"
      action="<?= $controller->link_for('questionnaire/assign') ?>">
    <?= CSRFProtection::tokenTag() ?>
    <? if ($step == 0): ?>
        <article class="studip">
            <header><h1><?= _('Veranstaltungen suchen') ?></h1></header>
            <section>
                <label>
                    <?= _('Semester') ?>
                    <select name="semester_id">
                        <? foreach ($available_semesters as $available_semester): ?>
                            <option value="<?= htmlReady($available_semester->id) ?>"
                                    <?= ($available_semester->id == $semester_id)
                                      ? 'selected="selected"'
                                      : ''
                                    ?>>
                                <?= htmlReady($available_semester->name) ?>
                            </option>
                        <? endforeach ?>
                    </select>
                </label>
                <label>
                    <?= _('Einrichtung') ?>
                    (<?= _('optional') ?>)
                    <select name="institute_id">
                        <option value=""
                                <?= ($institute_id == '' ? 'selected="selected"' : '') ?>>
                                <?= _('(bitte wählen)') ?>
                        </option>
                        <? foreach ($available_institutes as $available_institute): ?>
                            <option value="<?= htmlReady($available_institute['Institut_id']) ?>"
                                    <?= ($available_institute['Institut_id'] == $institute_id)
                                      ? 'selected="selected"'
                                      : ''
                                    ?>>
                                <?= htmlReady($available_institute['Name']) ?>
                            </option>
                        <? endforeach ?>
                    </select>
                </label>
                <label>
                    <?= _('Veranstaltungstyp') ?>
                    (<?= _('optional') ?>)
                    <select name="course_type_id">
                        <option value=""
                                <?= ($course_type_id == '' ? 'selected="selected"' : '') ?>>
                            <?= dgettext('AskALotPlugin', '(bitte wählen)') ?>
                        </option>
                        <? foreach ($available_course_types as $available_course_type): ?>
                            <option value="<?= htmlReady($available_course_type['id']) ?>"
                                    <?= ($available_course_type['id'] == $course_type_id)
                                      ? 'selected="selected"'
                                      : ''
                                    ?>>
                                <?= htmlReady($available_course_type['name']) ?>
                            </option>
                        <? endforeach ?>
                    </select>
                </label>
                <?= \Studip\Button::create(_('Suchen'), 'search_courses') ?>
            </section>
        </article>
    <? elseif ($step == 1): ?>
        <?= $this->render_partial('questionnaire/assign_step1') ?>
    <? elseif ($step == 2): ?>
        <?= $this->render_partial('questionnaire/assign_step2') ?>
    <? elseif ($step == 3): ?>

    <? endif ?>
    <? if ($step >= 1): ?>
        <input type="hidden" name="semester_id" value="<?= htmlReady($semester_id) ?>">
        <input type="hidden" name="institute_id" value="<?= htmlReady($institute_id) ?>">
        <input type="hidden" name="course_type_id" value="<?= htmlReady($course_type_id) ?>">
    <? endif ?>
    <? if ($step >= 2): ?>
        <? if ($selected_courses): ?>
            <? foreach ($selected_courses as $course): ?>
                <input type="hidden" name="course_id_list[]" value="<?= htmlReady($course->id) ?>">
            <? endforeach ?>
        <? endif ?>
    <? endif ?>
</form>
