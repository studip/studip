<? if (empty($migrations)): ?>

<p>
    <?= _('Ihr System befindet sich auf dem aktuellen Stand.') ?>
</p>

<? else: ?>

<p>
    <?= _('Die hier aufgeführten Anpassungen werden beim Klick auf "Starten" ausgeführt:') ?>
</p>
<table class="default">
    <colgroup>
        <col width="120px">
        <col>
    <thead>
        <tr>
            <th style="text-align: right;"><?= _('Nr.') ?></th>
            <th><?= _('Beschreibung') ?></th>
        </tr>
    </thead>
    <tbody>
    <? foreach ($migrations as $number => $migration): ?>
        <tr>
            <td style="text-align: right;">
                <?= $number ?>
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
            <td colspan="3">
            <? if ($lock->isLocked($lock_data)): ?>
                <?= MessageBox::info(sprintf(_('Die Migration wurde %s von %s bereits angestossen und läuft noch.'),
                                               reltime($lock_data['timestamp']),
                                               htmlReady(User::find($lock_data['user_id'])->getFullName())),
                                       [sprintf(_('Sollte während der Migration ein Fehler aufgetreten sein, so können Sie ' .
                                                       'diese Sperre durch den unten stehenden Link oder das Löschen der Datei ' .
                                                       '<em>%s</em> auflösen.'), $lock->getFilename())]) ?>
                <?= Studip\LinkButton::create(_('Sperre aufheben'), URLHelper::getURL('?release_lock=1&target=' . @$target)) ?>
            <? else: ?>
                <form method="post">
                    <?= CSRFProtection::tokenTag() ?>
                <? if (isset($target)): ?>
                    <input type="hidden" name="target" value="<?= $target ?>">
                <? endif ?>
                    <?= Studip\Button::createAccept(_('Starten'), 'start')?>
                </form>
            <? endif; ?>
            </td>
        </tr>
    </tfoot>
</table>
<? endif ?>
