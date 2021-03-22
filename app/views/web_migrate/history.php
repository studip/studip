<? if (STUDIP\ENV === 'development'): ?>
<form method="post" action="<?= $controller->link_for('revert') ?>">
    <?= CSRFProtection::tokenTag() ?>
<? endif; ?>
    <table class="default" id="migration-list">
        <caption>
        <? if (STUDIP\ENV === 'development'): ?>
            <?= _('Die markierten Anpassungen werden beim Klick auf "Starten" zurückgesetzt:') ?>
        <? else: ?>
            <?= _('Diese Anpassungen wurden im System bereits ausgeführt.') ?>
        <? endif; ?>
        </caption>
        <colgroup>
        <? if (STUDIP\ENV === 'development' && !$lock->isLocked($lock_data)): ?>
            <col style="width: 24px">
        <? endif; ?>
            <col style="width: 120px">
            <col>
        </colgroup>
        <thead>
            <tr>
            <? if (STUDIP\ENV === 'development' && !$lock->isLocked($lock_data)): ?>
                <th>
                    <input type="checkbox"
                           data-proxyfor="#migration-list tbody :checkbox"
                           data-activates="#migration-list tfoot .button">
                </th>
            <? endif; ?>
                <th><?= _('Nr.') ?></th>
                <th><?= _('Beschreibung') ?></th>
            </tr>
        </thead>
        <tbody>
        <? foreach ($history as $number => $migration): ?>
            <tr>
            <? if (STUDIP\ENV === 'development' && !$lock->isLocked($lock_data)): ?>
                <td>
                    <input type="checkbox"
                           name="versions[]" value="<?= htmlReady($number) ?>">
                </td>
            <? endif; ?>
                <td>
                    <?= htmlReady($number) ?>
                </td>
                <td>
                <? if ($migration->description()): ?>
                    <?= htmlReady($migration->description()) ?>
                <? else: ?>
                    <em><?= _('keine Beschreibung vorhanden') ?></em>
                <? endif ?>
                </td>
            </tr>
        <? endforeach; ?>
        </tbody>
    <? if (STUDIP\ENV === 'development'): ?>
        <tfoot>
            <tr>
                <td colspan="3">
                <? if ($lock->isLocked($lock_data)): ?>
                    <?= MessageBox::info(sprintf(
                        _('Die Migration wurde %s von %s bereits angestossen und läuft noch.'),
                        reltime($lock_data['timestamp']),
                        htmlReady(User::find($lock_data['user_id'])->getFullName()
                    )), [
                        sprintf(
                            _('Sollte während der Migration ein Fehler aufgetreten sein, so können Sie '
                            . 'diese Sperre durch den unten stehenden Link oder das Löschen der Datei '
                            . '<em>%s</em> auflösen.'),
                            $lock->getFilename()
                        )
                    ]) ?>
                    <?= Studip\LinkButton::create(_('Sperre aufheben'), $controller->link_for('release', $target)) ?>
                <? else: ?>
                    <?= Studip\Button::createAccept(_('Starten'), 'start')?>
                <? endif; ?>
                </td>
            </tr>
        </tfoot>
    <? endif; ?>
    </table>
<? if (STUDIP\ENV === 'development'): ?>
</form>
<? endif; ?>
