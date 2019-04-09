<form id="edit-cycle"
    action="<?= $controller->url_for('course/timesrooms/' . ($cycle->isNew() ? 'saveCycle' : 'editCycle/' . $cycle->id), $linkAttributes) ?>"
    class="default" method="post"
    <?= Request::int('fromDialog') ? 'data-dialog="size=big"' : '' ?>>
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend><?= _('Regelmäßiger Termin') ?></legend>

        <label class="col-2">
            <?= _('Starttag') ?>
            <select name="day">
                <? foreach ([1, 2, 3, 4, 5, 6, 0] as $d): ?>
                    <option
                        value="<?= $d ?>" <?= (Request::int('day') === $d) || (!is_null($cycle->start_time) && $cycle->weekday == $d) || ($d == 1) ? 'selected' : ''?>>
                        <?= getWeekday($d, false) ?>
                    </option>
                <? endforeach; ?>
            </select>
        </label>

        <label class="col-2">
            <span class="required">
                <?= _('Startzeit') ?>
            </span>
            <input class="size-s studip-timepicker" type="text" name="start_time"
                   value="<?= htmlReady(Request::get('start_time', mb_substr($cycle->start_time, 0, 5))) ?>"
                   required placeholder="HH:mm">
        </label>

        <label class="col-2">
            <span class="required">
                <?= _('Endzeit') ?>
            </span>
            <input class="size-s studip-timepicker" type="text" name="end_time"
                   value="<?= htmlReady(Request::get('end_time', mb_substr($cycle->end_time, 0, 5))) ?>"
                   required placeholder="HH:mm">
        </label>

        <label>
            <?= _('Art') ?>
            <select name="course_type" id="course_type" class="size-s">
                <? foreach ($GLOBALS['TERMIN_TYP'] as $id => $value) : ?>
                    <option value="<?= $id ?>" <? if(Request::get('course_type') && Request::get('course_type') == $id) :?>selected="selected"<? endif?>><?= htmlReady($value['name']) ?></option>
                <? endforeach; ?>
            </select>
        </label>

        <label>
            <?= _('Beschreibung') ?>
            <input type="text" name="description"
                   value="<?= htmlReady(Request::get('description', $cycle->description)) ?>">
        </label>

        <label>
            <?= _('Turnus') ?>
            <select name="cycle">
                <option value="0" <?= (Request::int('cycle', $cycle->cycle) === 0) ? 'selected' : '' ?>>
                    <?= _('Wöchentlich') ?>
                </option>
                <option value="1" <?= (Request::int('cycle', $cycle->cycle) === 1) ? 'selected' : '' ?>>
                    <?= _('Zweiwöchentlich') ?>
                </option>
                <option value="2" <?= (Request::int('cycle', $cycle->cycle) === 2) ? 'selected' : '' ?>>
                    <?= _('Dreiwöchentlich') ?>
                </option>
            </select>
        </label>

        <label>
            <?= _('Startwoche') ?>
            <select name="startWeek">
                <!-- write down all Semesters as possible Start -->
                <? if (isset($end_semester_weeks['start'])) : ?>
                    <? foreach ($end_semester_weeks['start'] as $end_sem_week) : ?>
                        <option value="<?= $end_sem_week['value'] ?>"
                            <?= (Request::get('startWeek', $cycle->week_offset) == $end_sem_week['value']) ? 'selected' : '' ?>>
                                <?= htmlReady($end_sem_week['label']) ?></option>
                    <? endforeach; ?>
                <? endif; ?>

                <!-- write down all weeks for all Semesters -->
                <? foreach ($clean_weeks as $semester => $weeks) : ?>
                    <optgroup label="<?= htmlReady($semester) ?>">
                        <? foreach ($weeks as $value => $label) : ?>
                            <option value="<?= $value ?>"
                                <?= (Request::get('startWeek', $cycle->week_offset) == $value) ? 'selected' : '' ?>>
                                    <?= htmlReady($label) ?>
                            </option>
                        <? endforeach; ?>
                    </optgroup>
                <? endforeach; ?>
            </select>
        </label>

        <label>
            <?= _('Endwoche') ?>
            <select name="endWeek">
                <? if (isset($end_semester_weeks['ende'])) : ?>
                    <? $selected = isset($cycle->end_offset) ? $cycle->end_offset : -1 ?>
                    <? foreach ($end_semester_weeks['ende'] as $end_sem_week) : ?>
                        <option value="<?= $end_sem_week['value'] ?>"
                            <?= (Request::get('endWeek', $selected) == $end_sem_week['value']) ? 'selected' : '' ?>>
                                <?= htmlReady($end_sem_week['label']) ?>
                        </option>
                    <? endforeach; ?>
                <? endif; ?>

                <? foreach ($clean_weeks as $semester => $weeks) : ?>
                    <optgroup label="<?= htmlReady($semester) ?>">
                        <? foreach ($weeks as $value => $label) : ?>
                            <option value="<?= $value  ?>"
                                <?= (Request::get('endWeek', $selected) == $value) ? 'selected' : '' ?>>
                                    <?= htmlReady($label) ?>
                            </option>
                        <? endforeach; ?>
                    </optgroup>
                <? endforeach; ?>
            </select>
        </label>

        <label>
            <?= _('SWS Lehrende') ?>
            <input type="text" name="teacher_sws" class="size-s"
                   value="<?= $cycle->sws ? htmlReady(Request::get('teacher_sws', $cycle->sws)) :'' ?>">
        </label>
    </fieldset>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern'), 'save') ?>
        <? if (Request::int('fromDialog')): ?>
            <?= Studip\LinkButton::create(_('Zurück zur Übersicht'), $controller->url_for('course/timesrooms/index'), ['data-dialog' => 'size=big']) ?>
        <? endif; ?>
    </footer>
</form>
