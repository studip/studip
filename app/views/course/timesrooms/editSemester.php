<form action="<?= $controller->url_for() ?>" method="post"
      class="default" data-dialog>
    <?=CSRFProtection::tokenTag()?>
    <fieldset>
        <legend><?= _('Semester ändern') ?></legend>

        <label for="startSemester">
            <?= _('Startsemester') ?>
            <select name="startSemester" id="startSemester">
                <? foreach ($semester as $sem) : ?>
                    <option
                        value="<?= $sem->semester_id ?>" <?= $sem->semester_id == $course->start_semester->semester_id ? 'selected' : '' ?>>
                        <?= htmlReady($sem->name) ?>
                    </option>
                <? endforeach; ?>
            </select>
        </label>

        <label for="endSemester">
            <?= _('Dauer') ?>
            <select name="endSemester" id="endSemester">
                <option value="0"
                    <?= (int)$course->duration_time == 0 ? 'selected' : '' ?>>
                    <?= _('Ein Semester') ?></option>
                <? foreach ($semester as $sem) : ?>
                    <? if ($sem->beginn >= $course->start_semester->beginn) : ?>
                        <option value="<?= $sem->semester_id ?>"
                            <?= (int)$course->duration_time != 0 && (($course->start_time + $course->duration_time) == $sem->beginn) ? 'selected' : '' ?>>
                            <?= htmlReady($sem->name) ?>
                        </option>
                    <? endif; ?>
                <? endforeach; ?>
                <option value="-1"
                    <?= (int)$course->duration_time == -1 ? 'selected' : '' ?>>
                    <?= _('Unbegrenzt') ?></option>
            </select>
        </label>
    </fieldset>

    <footer style="margin-top: 1ex" data-dialog-button>
        <?= Studip\Button::createAccept(_('Semester speichern'), 'save') ?>
    </footer>
</form>
