<?php
// In den Controller
$is_exTermin = $termin instanceof CourseExDate;
?>
<tr>
<? if (!$locked) : ?>
    <td>
        <label for="<?= htmlReady($termin->termin_id) ?>">
            <input class="<?= $class_ids ?>" type="checkbox" id="<?= htmlReady($termin->termin_id) ?>"
                   value="<?= htmlReady($termin->termin_id) ?>"
                   <? if (is_array($checked_dates)): ?>
                       <? if (in_array($termin->termin_id, $checked_dates)) echo 'checked'; ?>
                   <? else: ?>
                       <? if (!$is_exTermin && $termin->date > time() && ($termin->date <= $current_semester->ende || $semester_filter !== 'all')) echo 'checked'; ?>
                   <? endif ?>
                   name="single_dates[]">
        </label>
    </td>
<? endif ?>

    <td class="<?= $termin->getRoom() !== null ? 'green' : 'red' ?>">
    <? if ($is_exTermin) : ?>
        <span class="is_ex_termin">
            <?= htmlReady($termin->getFullname(CourseDate::FORMAT_VERBOSE)) ?>
        </span>
    <? elseif ($locked): ?>
        <?= htmlReady($termin->getFullname(CourseDate::FORMAT_VERBOSE)) ?>
    <? else: ?>
        <a data-dialog
           href="<?= $controller->url_for('course/timesrooms/editDate/' . $termin->termin_id, $linkAttributes) ?>">
            <?= htmlReady($termin->getFullname(CourseDate::FORMAT_VERBOSE)) ?>
        </a>
    <? endif ?>
    </td>

    <td>
    <? if (count($termin->dozenten) > 0): ?>
        <ul class="list-unstyled list-csv <? if ($is_exTermin) echo 'is_ex_termin'; ?>">
        <? foreach ($termin->dozenten as $dozent) : ?>
            <li><?= $dozent instanceof User ? htmlReady($dozent->getFullname()) : '' ?></li>
        <? endforeach ?>
        </ul>
    <? endif; ?>
    </td>
    <td>
    <? if ($room_holiday = SemesterHoliday::isHoliday($termin->date, false)): ?>
        <? $room_holiday = '<span' . ($is_exTermin ? ' class="is_ex_termin"' : '') . '>(' .
                           htmlReady($room_holiday['name']) . ')</span>' ?>
    <? endif; ?>

    <? if ($is_exTermin && ($comment = $termin->content)) : ?>
        <span class="is_ex_termin" style="font-style: italic"><?= _('(fällt aus)') ?></span>
        <?= tooltipIcon($termin->content, false) ?>
    <? elseif ($name = SemesterHoliday::isHoliday($termin->date, false) && $is_exTermin): ?>
        <?= $room_holiday ?>
    <? elseif ($room = $termin->getRoom()) : ?>
        <?= $room->getFormattedLink(true, true, true, 'view_schedule', 'no_nav', $termin->date, $room->getName()) ?>
        <?= $room_holiday ?: '' ?>
    <? elseif ($freeTextRoom = $termin->getRoomName()) : ?>
        <?= sprintf('(%s)', htmlReady($freeTextRoom)) ?>
    <? else : ?>
        <?= _('Keine Raumangabe') ?>
        <?= $room_holiday ?: '' ?>
    <? endif ?>

    <? $room_request = RoomRequest::find(RoomRequest::existsByDate($termin->id, true)) ?>
    <? if (isset($room_request)) : ?>
        <? $msg_info = _('Für diesen Termin existiert eine Raumanfrage: ') . $room_request->getInfo() ?>
        <?= tooltipIcon($msg_info) ?>
    <? endif ?>
    </td>
    <td class="actions">
        <? $actionMenu = ActionMenu::get() ?>
    <? if ($is_exTermin): ?>
        <? $actionMenu->addLink(
            $controller->url_for(
                'course/timesrooms/cancel/' . $termin->id
                . ($termin->metadate_id ? '/' . $termin->metadate_id : ''),
                $linkAttributes
            ),
            _('Kommentare bearbeiten'),
            Icon::create('edit', 'clickable', ['title' => _('Kommentar für diesen Termin bearbeiten')]),
            ['data-dialog' => 'size=50%']
        ) ?>

        <? $params = [
            'type'         => 'image',
            'class'        => 'middle',
            'name'         => 'delete_single_date',
            'data-confirm' => _('Diesen Termin wiederherstellen?'),
            'formaction'   => $controller->url_for('course/timesrooms/undeleteSingle/' . $termin->id),
        ]; ?>
        <? if (Request::isXhr()) : ?>
            <? $params['data-dialog'] = 'size=auto' ?>
        <? endif ?>

        <? $actionMenu->addButton(
            'delete_part',
            _('Termin wiederherstellen'),
            Icon::create('trash+decline', 'clickable', $params)
        ) ?>

    <? elseif (!$locked) : ?>
        <? $actionMenu->addLink(
            $controller->url_for('course/timesrooms/editDate/' . $termin->id, $linkAttributes),
            _('Termin bearbeiten'),
            Icon::create('edit', 'clickable', ['title' => _('Diesen Termin bearbeiten')]),
            ['data-dialog' => '']
        ) ?>

        <? $params = [
            'type'         => 'image',
            'class'        => 'middle',
            'name'         => 'delete_single_date',
            'data-confirm' => _('Wollen Sie diesen Termin wirklich löschen / ausfallen lassen?')
                              . '<br>' . implode('<br>', $termin->getDeletionWarnings()),
            'formaction'   => $controller->url_for(
                'course/timesrooms/deleteSingle/' . $termin->id,
                ['cycle_id' => $termin->metadate_id] + $linkAttributes
            ),
        ]; ?>
        <? if (Request::isXhr()) : ?>
            <? $params['data-dialog'] = 'size=big' ?>
        <? endif ?>

        <? $actionMenu->addLink(
            $controller->url_for(
                'course/timesrooms/stack',
                [
                    'single_dates[]' => $termin->termin_id,
                    'method' => 'preparecancel'
                ]
            ),
            _('Termin löschen'),
            Icon::create('trash', 'clickable'),
            ['data-dialog' => '1']
        ) ?>
    <? endif; ?>
        <?= $actionMenu->render() ?>
    </td>
</tr>
