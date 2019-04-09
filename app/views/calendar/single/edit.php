<? use Studip\Button, Studip\LinkButton; ?>
<? if (Request::isXhr()) : ?>
    <? foreach (PageLayout::getMessages() as $messagebox) : ?>
        <?= $messagebox ?>
    <? endforeach ?>
<? else : ?>
    <? SkipLinks::addIndex(_('Termine anlegen/bearbeiten'), 'main_content', 100); ?>
<? endif; ?>
<form data-dialog="" method="post" action="<?= $controller->url_for($base . 'edit/' . $range_id . '/' . $event->event_id) ?>" class="default collapsable">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend>
            <? if ($event->isNew()) : ?>
                <?= _('Neuen Termin anlegen') ?>
            <? else : ?>
                <?= _('Termin bearbeiten') ?>
            <? endif; ?>
        </legend>

        <label class="hidden-tiny-down">
            <input type="checkbox" name="isdayevent" value="1" <?= $event->isDayEvent() ? 'checked' : '' ?>
                onChange="jQuery(this).closest('fieldset').find('input[size=\'2\']').prop('disabled', function (i,val) { return !val; });">
            <?= _('Ganztägig') ?>
        </label>

        <section class="required">
            <?= _('Beginn') ?>
        </section>

        <label class="col-3">
            <?= _('Datum') ?>
            <input type="text" name="start_date" id="start-date" value="<?= strftime('%x', $event->getStart()) ?>" size="12" required>
        </label>

        <label class="col-3">
            <?= _('Uhrzeit') ?>

            <div class="hgroup">
                <input class="size-s no-hint" type="text" name="start_hour" value="<?= date('G', $event->getStart()) ?>" size="2" maxlength="2"<?= $event->isDayEvent() ? ' disabled' : '' ?>>
                :
                <input class="size-s no-hint" type="text" name="start_minute" value="<?= date('i', $event->getStart()) ?>" size="2" maxlength="2"<?= $event->isDayEvent() ? ' disabled' : '' ?>>
            </div>
        </label>

        <section class="required">
            <?= _('Ende') ?>
        </section>

        <label class="col-3">
            <?= _('Datum') ?>
            <input type="text" name="end_date" id="end-date" value="<?= strftime('%x', $event->getEnd()) ?>" size="12" required>
        </label>

        <label class="col-3">
            <?= _('Uhrzeit') ?>

            <div class="hgroup">
                <input class="size-s no-hint" type="text" name="end_hour" value="<?= date('G', $event->getEnd()) ?>" size="2" maxlength="2"<?= $event->isDayEvent() ? ' disabled' : '' ?>>
                :
                <input class="size-s no-hint" type="text" name="end_minute" value="<?= date('i', $event->getEnd()) ?>" size="2" maxlength="2"<?= $event->isDayEvent() ? ' disabled' : '' ?>>
            </div>
        </label>

        <label>
            <span class="required">
                <?= _('Zusammenfassung') ?>
            </span>

            <input type="text" size="50" name="summary" id="summary" value="<?= htmlReady($event->getTitle()) ?>">
        </label>

        <label>
            <?= _('Beschreibung') ?>
            <textarea rows="2" cols="40" id="description" name="description"><?= htmlReady($event->getDescription()) ?></textarea>
        </label>

        <label class="col-3">
            <?= _('Kategorie') ?>
            <select name="category_intern" id="category-intern" class="nested-select">
            <? foreach ($GLOBALS['PERS_TERMIN_KAT'] as $key => $category) : ?>
                <option value="<?= $key ?>" <?= $key == $event->getCategory() ? 'selected' : '' ?> data-text-color="<?= $category['color'] ?>">
                    <?= htmlReady($category['name']) ?>
                </option>
            <? endforeach; ?>
            </select>
        </label>

        <label class="col-3">
            <?= tooltipicon(_('Sie können beliebige Kategorien in das Freitextfeld eingeben. Trennen Sie einzelne Kategorien bitte durch ein Komma.')) ?>
            <input type="text" name="categories" value="<?= htmlReady($event->getUserDefinedCategories()) ?>"
                placeholder="<?= _('Eigener Kategoriename') ?>">
        </label>

        <label>
            <?= _('Raum/Ort') ?>
            <input type="text" name="location" id="location" value="<?= htmlReady($event->getLocation()) ?>">
        </label>

        <? if ($calendar->getPermissionByUser($GLOBALS['user']->id) == Calendar::PERMISSION_OWN) : ?>
        <? $info = _('Private und vertrauliche Termine sind nur für Sie sichtbar.') ?>

        <? /* SEMBBS nur private und vertrauliche Termine
        <? $info = _('Private und vertrauliche Termine sind nur für Sie sichtbar. Öffentliche Termine werden auf ihrer internen Homepage auch anderen Nutzern bekanntgegeben.') ?>
         *
         */ ?>

        <? elseif ($calendar->getRange() == Calendar::RANGE_SEM) : ?>
        <? $info = _('In Veranstaltungskalendern können nur private Termine angelegt werden.') ?>
        <? elseif ($calendar->getRange() == Calendar::RANGE_INST) : ?>
        <? $info = _('In Einrichtungskalendern können nur private Termine angelegt werden.') ?>
        <? else : ?>
        <? $info = _('Im Kalender eines anderen Nutzers können Sie nur private oder vertrauliche Termine einstellen. Vertrauliche Termine sind nur für Sie und den Kalenderbesitzer sichtbar. Alle anderen sehen den Termin nur als Besetztzeit.') ?>
        <? endif; ?>

        <label for="accessibility">
            <?= _('Zugriff') ?>
            <?= tooltipicon($info) ?>

            <select name="accessibility" id="accessibility" size="1">
                <? foreach ($event->getAccessibilityOptions($calendar->getPermissionByUser($GLOBALS['user']->id)) as $key => $option) : ?>
                <option value="<?= $key ?>"<?= $event->getAccessibility() == $key ? ' selected' : '' ?>><?= $option ?></option>
                <? endforeach; ?>
            </select>
        </label>

        <label>
            <?= _('Priorität') ?>

            <? $priority_names = [_('Keine Angabe'), _('Hoch'), _('Mittel'), _('Niedrig')] ?>
            <select name="priority" id="priority" size="1">
                <? foreach ($priority_names as $key => $priority) : ?>
                <option value="<?= $key ?>"<?= $key == $event->getPriority() ? ' selected' : '' ?>><?= $priority ?></option>
                <? endforeach; ?>
            </select>
        </label>

        <? if (!$event->isNew() && get_config('CALENDAR_GROUP_ENABLE')) : ?>
            <section>
                <? $author = $event->getAuthor() ?>
                <? if ($author) : ?>
                    <?= sprintf(_('Eingetragen am: %s von %s'),
                    strftime('%x, %X', $event->mkdate),
                        htmlReady($author->getFullName('no_title'))) ?>
                <? endif; ?>
            </section>
            <? if ($event->event->mkdate < $event->event->chdate) : ?>
                <? $editor = $event->getEditor() ?>
                <? if ($editor) : ?>
                <section>
                    <?= sprintf(_('Zuletzt bearbeitet am: %s von %s'),
                        strftime('%x, %X', $event->chdate),
                            htmlReady($editor->getFullName('no_title'))) ?>
                </section>
                <? endif; ?>
            <? endif; ?>
        <? endif; ?>
    </fieldset>


    <fieldset class="collapsed">
        <legend>
            <?= _('Wiederholung') ?>
            <? if ($event->getRecurrence('rtype') != 'SINGLE') : ?>
                (<?= $event->toStringRecurrence() ?>)
            <? endif ?>
        </legend>

        <h2><?= _('Wiederholungsart') ?></h2>

        <section>
            <? $linterval = $event->getRecurrence('linterval') ?: '1' ?>
            <? $rec_type = $event->toStringRecurrence(true) ?>
            <ul class="recurrences">
                <li>
                    <label class="rec-label">
                        <input type="radio" class="rec-select" id="rec-none" name="recurrence" value="single"<?= $event->getRecurrence('rtype') == 'SINGLE' ? ' checked' : '' ?>>
                        <?= _('Keine') ?>
                        <?= tooltipIcon(_('Der Termin wird nicht wiederholt.')) ?>
                    </label>
                </li>
                <li>
                    <label class="rec-label">
                        <input type="radio" class="rec-select" id="rec-daily" name="recurrence" value="daily"<?= $event->getRecurrence('rtype') == 'DAILY' ? ' checked' : '' ?>>
                        <?= _('Täglich') ?>
                    </label>

                    <div class="rec-content" id="rec-content-daily">
                        <div class="hgroup">
                            <label>
                                <input type="radio" name="type_daily" value="day"<?= in_array($rec_type, ['daily', 'xdaily']) ? ' checked' : '' ?>>
                                <?= sprintf(_('Jeden %s. Tag'), '<input type="text" size="3" name="linterval_d" value="' . $linterval . '">') ?>
                            </label>
                        </div>

                        <label>
                            <input type="radio" name="type_daily" value="workday"<?= $rec_type == 'workdaily' ? ' checked' : '' ?>>
                            <?= _('Jeden Werktag') ?>
                        </label>
                    </div>
                </li>
                <li>
                    <? $wdays = [
                        '1' => _('Montag'),
                        '2' => _('Dienstag'),
                        '3' => _('Mittwoch'),
                        '4' => _('Donnerstag'),
                        '5' => _('Freitag'),
                        '6' => _('Samstag'),
                        '7' => _('Sonntag')] ?>
                    <label class="rec-label" for="rec-weekly">
                        <input type="radio" class="rec-select" id="rec-weekly" name="recurrence" value="weekly"<?= $event->getRecurrence('rtype') == 'WEEKLY' ? ' checked' : '' ?>>
                        <?= _('Wöchentlich') ?>
                    </label>
                    <div class="rec-content" id="rec-content-weekly">
                        <div class="hgroup">
                            <label>
                                <?= sprintf(_('Jede %s. Woche am:'), '<input type="text" size="3" name="linterval_w" value="' . $linterval . '">') ?>
                            </label>
                        </div>
                        <div>
                            <? $aday = $event->getRecurrence('wdays') ?: date('N', $event->getStart()) ?>
                            <? foreach ($wdays as $key => $wday) : ?>
                            <label style="white-space: nowrap;">
                                <input type="checkbox" name="wdays[]" value="<?= $key ?>"<?= mb_strpos((string) $aday, (string) $key) !== false ? ' checked' : '' ?>>
                                <?= $wday ?>
                            </label>
                            <? endforeach; ?>
                        </div>
                    </div>
                </li>
                <li>
                    <? $mdays = [
                        '1' => _('Ersten'),
                        '2' => _('Zweiten'),
                        '3' => _('Dritten'),
                        '4' => _('Vierten'),
                        '5' => _('Letzten')] ?>
                    <? $mdays_options = '' ?>
                    <? $mday_selected = $event->getRecurrence('sinterval') ?>
                    <? foreach ($mdays as $key => $mday) :
                            $mdays_options .= '<option value="' . $key . '"';
                            if ($key == $mday_selected) {
                                $mdays_options .= ' selected';
                            }
                            $mdays_options .= '>' . $mday . '</option>';
                    endforeach; ?>
                    <? $wdays_options = '' ?>
                    <? $wday_selected = $event->getRecurrence('wdays') ?: date('N', $event->getStart()) ?>
                    <? foreach ($wdays as $key => $wday) :
                            $wdays_options .= '<option value="' . $key . '"';
                            if ($key == $wday_selected) {
                                $wdays_options .= ' selected';
                            }
                            $wdays_options .= '>' . $wday . '</option>';
                    endforeach; ?>

                    <label class="rec-label" for="rec-monthly">
                        <input type="radio" class="rec-select" id="rec-monthly" name="recurrence" value="monthly"<?= $event->getRecurrence('rtype') == 'MONTHLY' ? ' checked' : '' ?>>
                        <?= _('Monatlich') ?>
                    </label>
                    <div class="rec-content" id="rec-content-monthly">
                        <div class="hgroup">
                            <label>
                                <input type="radio" value="day" name="type_m"<?= in_array($rec_type, ['mday_monthly', 'mday_xmonthly']) ? ' checked' : '' ?>>
                                <? $mday = $event->getRecurrence('day') ?: date('j', $event->getStart()) ?>
                                <?= sprintf(_('Wiederholt am %s. jeden %s. Monat'),
                                    '<input type="text" name="day_m" size="2" value="'
                                    . $mday . '">',
                                    '<input type="text" name="linterval_m1" size="3" value="'
                                    . $linterval . '">') ?>
                            </label>
                        </div>
                        <div class="hgroup">
                            <label>
                                <input type="radio" value="wday" name="type_m"<?= in_array($rec_type, ['xwday_xmonthly', 'lastwday_xmonthly', 'xwday_monthly', 'lastwday_monthly']) ? ' checked' : '' ?>>
                                <?= sprintf(_('Jeden %s alle %s Monate'),
                                    '<select size="1" name="sinterval_m">' . $mdays_options . '</select>'
                                    . '<select size="1" name="wday_m">' . $wdays_options . '</select>',
                                    '<input type="text" class="no-hint" size="3" maxlength="3" name="linterval_m2" value="'
                                    . $linterval . '">')?>
                            </label>
                        </div>
                    </div>
                </li>
                <li>
                    <? $months = [
                        '1' => _('Januar'),
                        '2' => _('Februar'),
                        '3' => _('März'),
                        '4' => _('April'),
                        '5' => _('Mai'),
                        '6' => _('Juni'),
                        '7' => _('Juli'),
                        '8' => _('August'),
                        '9' => _('September'),
                        '10' => _('Oktober'),
                        '11' => _('November'),
                        '12' => _('Dezember')] ?>
                    <? $months_options = '' ?>
                    <? $month_selected = $event->getRecurrence('month') ?: date('n', $event->getStart()) ?>
                    <? foreach ($months as $key => $month) :
                            $months_options .= '<option value="' . $key . '"';
                            if ($key == $month_selected) {
                                $months_options .= ' selected';
                            }
                            $months_options .= '>' . $month . '</option>';
                    endforeach; ?>

                    <label class="rec-label" for="rec-yearly">
                        <input type="radio" class="rec-select" id="rec-yearly" name="recurrence" value="yearly"<?= $event->getRecurrence('rtype') == 'YEARLY' ? ' checked' : '' ?>>
                        <?= _('Jährlich') ?>
                    </label>
                    <div class="rec-content" id="rec-content-yearly">
                        <div class="hgroup">
                            <label>
                                <input type="radio" value="day" name="type_y"<?= $rec_type == 'mday_month_yearly' ? ' checked' : '' ?>>
                                <?= sprintf(_('Jeden %s. %s'),
                                    '<input type="text" size="2" maxlength="2" name="day_y" value="'
                                    . ($event->getRecurrence('day') ?: date('j', $event->getStart())) . '">',
                                    '<select size="1" name="month_y1">' . $months_options . '</select>') ?>
                            </label>
                        </div>

                        <div class="hgroup">
                            <label>
                                <input type="radio" value="wday" name="type_y"<?= in_array($rec_type, ['xwday_month_yearly', 'lastwday_month_yearly']) ? ' checked' : '' ?>>
                                <?= sprintf(_('Jeden %s im %s'),
                                    '<select size="1" name="sinterval_y">' . $mdays_options . '</select>'
                                    . '<select size="1" name="wday_y">' . $wdays_options . '</select>',
                                    '<select size="1" name="month_y2">' . $months_options . '</select>') ?>
                            </label>
                        </div>
                    </div>
                </li>
            </ul>
        </section>

        <h2><?= _('Wiederholung endet') ?></h2>

        <label>
            <? $checked = (!$event->getRecurrence('expire') || $event->getRecurrence('expire') >= Calendar::CALENDAR_END) && !$event->getRecurrence('count') ?>
            <input type="radio" name="exp_c" value="never"<?= $checked ? ' checked' : '' ?>>
            <?= _('Nie') ?>
        </label>

        <? $checked = $event->getRecurrence('expire') && $event->getRecurrence('expire') < Calendar::CALENDAR_END && !$event->getRecurrence('count') ?>

        <section class="hgroup">
            <label>
                <input type="radio" name="exp_c" value="date"<?= $checked ? ' checked' : '' ?>>
                <? $exp_date = $event->getRecurrence('expire') != Calendar::CALENDAR_END ? $event->getRecurrence('expire') : $event->getEnd() ?>
                <?= sprintf(_('Am %s'),
                        '<input type="text" class="size-s" name="exp_date" id="exp-date" value="'
                        . strftime('%x', $exp_date) . '">') ?>
            </label>
        </section>

        <section class="hgroup">
            <? $checked = $event->getRecurrence('count') ?>
            <label>
                <input type="radio" name="exp_c" value="count"<?= $checked ? ' checked' : '' ?>>
                <? $exp_count = $event->getRecurrence('count') ?: '10' ?>
                <?= sprintf(_('Nach %s Wiederholungen'),
                        '<input type="text" size="5" name="exp_count" value="'
                        . $exp_count . '">') ?>
            </label>
        </section>


        <label for="exc-dates">
            <?= _('Ausnahmen') ?>
        </label>

        <ul id="exc-dates">
            <? $exceptions = $event->getExceptions(); ?>
            <? sort($exceptions, SORT_NUMERIC); ?>
            <? foreach ($exceptions as $exception) : ?>
            <li>
                <label class="undecorated">
                    <input type="checkbox" name="del_exc_dates[]" value="<?= strftime('%d.%m.%Y', $exception) ?>" style="display: none;">
                    <span><?= strftime('%x', $exception) ?><?= Icon::create('trash', 'clickable', ['title' => _('Ausnahme löschen')])->asImg(16, ['style' => 'vertical-align: text-top;']) ?></span>
                </label>
                <input type="hidden" name="exc_dates[]" value="<?= strftime('%d.%m.%Y', $exception) ?>">
            </li>
            <? endforeach; ?>
        </ul>

        <div class="hgroup">
            <input style="vertical-align: top; opacity: 0.8;"
                   type="text" size="12" name="exc_date" id="exc-date" value=""
                   placeholder="<?= _("Datum eingeben") ?>">
            <span style="vertical-align: top;" onclick="STUDIP.CalendarDialog.addException(); return false;">
                <?= Icon::create('add', 'clickable', ['title' => _('Ausnahme hinzufügen')])->asInput(['class' => 'text-bottom']) ?>
            </span>
        </div>
    </fieldset>

    <? if (get_config('CALENDAR_GROUP_ENABLE') && $calendar->getRange() == Calendar::RANGE_USER) : ?>
        <?= $this->render_partial('calendar/group/_attendees') ?>
    <? endif; ?>

    <footer data-dialog-button>
        <?= Button::create(_('Speichern'), 'store', ['title' => _('Termin speichern')]) ?>

        <? if (!$event->isNew()) : ?>
        <? if ($event->getRecurrence('rtype') != 'SINGLE') : ?>
        <?= LinkButton::create(_('Aus Serie löschen'), $controller->url_for('calendar/single/delete_recurrence/' . implode('/', $event->getId()) . '/' . $atime)) ?>
        <? endif; ?>
        <?= LinkButton::create(_('Löschen'), $controller->url_for('calendar/single/delete/' . implode('/', $event->getId()))) ?>
        <? endif; ?>
        <? if (!Request::isXhr()) : ?>
        <?= LinkButton::create(_('Abbrechen'), $controller->url_for('calendar/single/' . $last_view, [$event->getStart()])) ?>
        <? endif; ?>
    </footer>
</form>
<script>
    jQuery('#start-date').datepicker({
        altField: '#end-date'
    });
    jQuery('#end-date').datepicker();
    jQuery('#exp-date').datepicker();
    jQuery('#exc-date').datepicker();

    $('ul.recurrences input[type=radio][id^=rec]').bind('change', function() {
        $('.rec-content').hide();

        if ($(this).is(':checked')) {
            $(this).parent().siblings('.rec-content').show();
        }
    })
</script>
