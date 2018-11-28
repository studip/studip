<form data-dialog action="<?= $controller->url_for('calendar/single/seminar_events/')?>" class="default">
    <section class="hgroup">
        <label>
            <?= _('Semesterfilter') ?>:
            <select name="sem_select" class="submit-upon-select">
                <option <?= ($sem == 'current' ? 'selected' : '')?> value="current"><?= _('Aktuelles Semester') ?></option>
                <option <?= ($sem == 'future' ? 'selected' : '')?> value="future"><?= _('Aktuelles und nächstes Semester') ?></option>
                <option <?= ($sem == 'last' ? 'selected' : '')?> value="last"><?= _('Aktuelles und letztes Semester') ?></option>
                <option <?= ($sem == 'lastandnext' ? 'selected' : '')?> value="lastandnext"><?= _('Letztes, aktuelles, nächstes Semester') ?></option>
                <? if (Config::get()->MY_COURSES_ENABLE_ALL_SEMESTERS) : ?>
                    <option <?= ($sem == 'all' ? 'selected' : '')?> value="all"><?= _('Alle Semester') ?></option>
                <? endif ?>

                <? if (!empty($semesters)) : ?>
                    <optgroup label="<?=_('Semester auswählen')?>">
                    <? foreach ($semesters as $semester) :?>
                        <option value="<?=$semester->id?>" <?= ($sem == $semester->id ? 'selected' : '')?>>
                            <?= htmlReady($semester->name)?>
                        </option>
                    <? endforeach ?>
                    </optgroup>
                <? endif ?>
            </select>
        </label>
    </section>

    <noscript>
        <?= \Studip\Button::createAccept(_('Auswählen'))?>
    </noscript>
</form>
