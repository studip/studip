<?php
$icon = $date->chdate > $last_visitdate ? 'date+new' : 'date';
$dialog_url = $show_raumzeit
            ? $controller->url_for('course/dates/details/' . $date->id)
            : $controller->url_for('course/dates/singledate/' . $date->id);
?>
<tr id="date_<?= $date->id ?>" <? if ($is_next_date) echo 'class="nextdate" title="' . _('Der nächste Termin') . '"'; ?> data-termin-id="<?= htmlReady($date->id) ?>">
    <td data-sort-value="<?= htmlReady($date->date) ?>" class="date_name">
        <a href="<?= $dialog_url ?>" data-dialog>
            <?= Icon::create($icon)->asImg(['class' => 'text-bottom']) ?>
            <?= htmlReady($date->getFullname(CourseDate::FORMAT_VERBOSE)) ?>
        </a>
    <? if (count($date->dozenten) > 0): ?>
        <br>
        (<?= htmlReady(implode(', ', $date->dozenten->getFullname())) ?>)
    <? endif; ?>
    </td>
    <td class="hidden-small-down">
        <ul class="themen-list clean">
        <? foreach ($date->topics as $topic): ?>
            <?= $this->render_partial('course/dates/_topic_li', compact('topic', 'date')) ?>
        <? endforeach; ?>
        </ul>
    </td>
    <td class="hidden-small-down">
        <?= htmlReady($date->getTypeName()) ?>
    </td>
<? if (count($course->statusgruppen) > 0) : ?>
    <td class="hidden-small-down">
    <? if (count($date->statusgruppen) > 0) : ?>
        <ul class="clean">
        <? foreach ($date->statusgruppen as $statusgruppe) : ?>
            <li><?= htmlReady($statusgruppe->name) ?></li>
        <? endforeach ?>
        </ul>
    <? else : ?>
        <?= _('alle') ?>
    <? endif ?>
    </td>
<? endif ?>
    <td>
    <? if ($date->getRoom()): ?>
        <?= $date->getRoom()->getFormattedLink() ?>
    <? else: ?>
        <?= htmlReady($date->raum) ?>
    <? endif; ?>
    </td>
    <td class="actions">
        <? $actionMenu = ActionMenu::get() ?>
        <? $filecount = count($date->getAccessibleFolderFiles($GLOBALS['user']->id)['files']); ?>
        <? if ($has_access): ?>
            <? $actionMenu->addLink($dialog_url, _('Termin bearbeiten'), Icon::create('edit'), ['data-dialog' => '']) ?>
            <? $actionMenu->addLink($controller->url_for('course/dates/new_topic?termin_id=' . $date->id),
                                    _('Thema hinzufügen'), Icon::create('topic+add'), ['data-dialog' => 'size=auto']) ?>
        <? endif ?>
        <? if ($filecount): ?>
            <? $actionMenu->addLink($controller->url_for('course/dates/details_files/' . $date->id),
                                    _('Dateien anzeigen'), Icon::create('folder-topic-full'), ['data-dialog' => '']) ?>
        <? endif ?>
        <? if ($has_access): ?>
            <? if (!$cancelled_dates_locked): ?>
                <? $actionMenu->addLink($controller->url_for('course/cancel_dates', ['termin_id' => $date->id]),
                                        _('Termin ausfallen lassen'), Icon::create('trash'), [
                                        'data-dialog' => 'size=auto',
                                        'data-confirm' => _('Wollen Sie diesen Termin wirklich ausfallen lassen?')
                                                          . '<br>' . implode('<br>', $date->getDeletionWarnings()),
                                        ]) ?>
            <? endif ?>
        <? endif ?>
        <?= $actionMenu->render() ?>
    </td>
</tr>
