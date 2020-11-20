<? if (count($migrations) === 0): ?>
    <?= MessageBox::info(_('Ihr System befindet sich auf dem aktuellen Stand.'))->hideClose() ?>
<? else: ?>
<form method="post" action="<?= $controller->link_for('migrate') ?>">
    <?= CSRFProtection::tokenTag() ?>
<? if (isset($target)): ?>
    <input type="hidden" name="target" value="<?= htmlReady($target) ?>">
<? endif ?>
<? if (STUDIP\ENV !== 'development'): ?>
    <?= addHiddenFields('versions', array_keys($migrations)) ?>
<? endif; ?>


    <table class="default" id="migration-list">
        <caption>
        <? if (STUDIP\ENV === 'development'): ?>
            <?= _('Die markierten Anpassungen werden beim Klick auf "Starten" ausgeführt:') ?>
        <? else: ?>
            <?= _('Die hier aufgeführten Anpassungen werden beim Klick auf "Starten" ausgeführt:') ?>
        <? endif; ?>
        </caption>
        <colgroup>
        <? if (STUDIP\ENV === 'development'): ?>
            <col style="width: 24px">
        <? endif; ?>
            <col style="width: 120px">
            <col>
        </colgroup>
        <thead>
            <tr>
            <? if (STUDIP\ENV === 'development'): ?>
                <th>
                    <input type="checkbox"
                           data-proxyfor="#migration-list tbody :checkbox"
                           data-activates="#migration-list tfoot .button">
                </th>
            <? endif; ?>
                <th style="text-align: right;"><?= _('Nr.') ?></th>
                <th><?= _('Beschreibung') ?></th>
            </tr>
        </thead>
        <tbody>
        <? foreach ($migrations as $number => $migration): ?>
            <tr>
            <? if (STUDIP\ENV === 'development'): ?>
                <td>
                    <input type="checkbox" checked
                           name="versions[]" value="<?= htmlReady($number) ?>">
                </td>
            <? endif; ?>
                <td style="text-align: right;">
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
        <tfoot>
            <tr>
                <td colspan="<?= 2 + (int) (STUDIP\ENV === 'development') ?>">
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
                    <?= Studip\LinkButton::create(_('Sperre aufheben'), $controller->releaseURL($target)) ?>
                <? else: ?>
                    <?= Studip\Button::createAccept(_('Starten'), 'start')?>
                <? endif; ?>
                </td>
            </tr>
        </tfoot>
    </table>
</form>
<? endif ?>