<? if ($show_form): ?>
    <form class="default" method="post"
          action="<?= $this->controller->link_for(
                  'resources/booking/copy/' . $booking->id
                  ) ?>" data-dialog>
        <?= CSRFProtection::tokenTag() ?>
        <fieldset>
            <legend><?= _('Zielsemester' )?></legend>
            <label>
                <?= _('In welches Semester soll die Buchung kopiert werden?') ?>
                <select name="semester_id">
                    <? foreach ($available_semesters as $semester): ?>
                        <option value="<?= htmlReady($semester->id) ?>"
                                <?= $semester_id == $semester->id
                                  ? 'selected="selected"'
                                  : '' ?>
                                data-start_week="<?= htmlReady($semester->getFirstSemesterWeek()) ?>"
                                data-end_week="<?= htmlReady($semester->getLastSemesterWeek()) ?>">
                            <?= htmlReady($semester->name) ?>
                        </option>
                    <? endforeach ?>
                </select>
            </label>
            <label>
                <?= _('In welcher Kalenderwoche soll die Buchung stattfinden?') ?>
                <select name="semester_week">
                    <? foreach ($available_semester_weeks as $index => $name): ?>
                        <option value="<?= htmlReady($index) ?>"
                                <?= $semester_week == $index
                                  ? 'selected="selected"'
                                  : '' ?>>
                            <?= htmlReady($name) ?>
                        </option>
                    <? endforeach ?>
                </select>
            </label>
        </fieldset>
        <div data-dialog-button>
            <?= \Studip\Button::create(_('Kopieren'), 'copy') ?>
        </div>
    </form>
<? endif ?>
