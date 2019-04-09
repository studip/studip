<form action="<?= $controller->url_for('admin/holidays/delete/bulk') ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>

<table class="default" id="holidays">
    <caption><?= _('Ferien') ?></caption>
    <colgroup>
        <col width="20px">
        <col>
        <col width="50%">
        <col width="48px">
    </colgroup>
    <thead>
        <tr>
            <th>
                <input type="checkbox"
                       data-proxyfor="#holidays tbody :checkbox"
                       data-activates="#holidays tfoot button">
            </th>
            <th><?= _('Name') ?></th>
            <th><?= _('Zeitraum') ?></th>
            <th>&nbsp;</th>
        </tr>
    </thead>
    <tbody>
<? if (empty($holidays)): ?>
        <tr>
            <td colspan="4" style="text-align: center;">
            <? if ($filter): ?>
                <?= _('In der gewählten Ansicht gibt es keine Einträge.') ?>
            <? else: ?>
                <?= _('Es wurden noch keine Ferien angelegt.') ?><br>
                <?= Studip\LinkButton::create(_('Neue Ferien anlegen'),
                                              $controller->url_for('admin/holidays/edit'),
                                              ['data-dialog' => 'size=auto']) ?>
            <? endif; ?>
            </td>
        </tr>
<? else: ?>
    <? foreach ($holidays as $holiday): ?>
        <tr <? if ($holiday->current) echo 'style="font-weight: bold;"'; ?>>
            <td>
                <input type="checkbox" name="ids[]" value="<?= $holiday->id ?>">
            </td>
            <td title="<?= htmlReady($holiday->description) ?>">
                <?= htmlReady($holiday->name) ?>
            </td>
            <td>
                <?= strftime('%x', $holiday->beginn) ?>
                -
                <?= strftime('%x', $holiday->ende) ?>
            </td>
            <td class="actions">
                <a data-dialog="size=auto" href="<?= $controller->url_for('admin/holidays/edit/' . $holiday->id) ?>">
                    <?= Icon::create('edit', 'clickable', ['title' => _('Ferienangaben bearbeiten')])->asImg() ?>
                </a>
                <?= Icon::create('trash', 'clickable', ['title' => _('Ferien löschen')])->asInput([
                    'formaction'   => $controller->url_for('admin/holidays/delete/' . $holiday->id),
                    'data-confirm' => _('Sollen die Ferien wirklich gelöscht werden?'),
                    'class' => 'text-bottom',
                ]) ?>
            </td>
        </tr>
    <? endforeach; ?>
<? endif; ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4">
                <?= _('Markierte Einträge') ?>
                <?= Studip\Button::create(_('Löschen'), 'delete', [
                    'data-confirm' => _('Sollen die Ferien wirklich gelöscht werden?'),
                ]) ?>
            </td>
        </tr>
    </tfoot>
</table>
</form>
