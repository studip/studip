<tr id="date_<?= $date->id ?>" class="ausfall" data-termin-id="<?= htmlReady($date->id) ?>">
    <td data-sort-value="<?= htmlReady($date->date) ?>" class="date_name">
        <?= Icon::create('date', Icon::ROLE_INFO)->asImg(['class' => 'text-bottom']) ?>
        <?= htmlReady($date->getFullname()) ?>
        <?= tooltipIcon($date->content) ?>
    <? if (count($date->dozenten) > 0): ?>
        <br>
        (<?= htmlReady(implode(', ', $date->dozenten->getFullname())) ?>)
    <? endif; ?>
    </td>
    <td class="hidden-small-down"></td>
    <td class="hidden-small-down">
        <?= htmlReady($date->getTypeName()) ?>
    </td>
<? if (count($course->statusgruppen) > 0) : ?>
    <td class="hidden-small-down"></td>
<? endif ?>
    <td></td>
    <td class="actions">
    <? if ($has_access && !$cancelled_dates_locked): ?>
        <form action="<?= $controller->url_for("course/timesrooms/undeleteSingle/{$date->id}/1") ?>" method="post">
            <? $actionMenu = ActionMenu::get() ?>
            <? $actionMenu->addButton('restore_date', _('Termin wiederherstellen'), Icon::create('trash+decline'),
                                      ['data-confirm' => _('Diesen Termin wiederherstellen?')]) ?>
            <?= $actionMenu->render() ?>
        </form>
    <? endif ?>
    </td>
</tr>
