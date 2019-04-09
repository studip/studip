<blockquote>
    <p>
        <?= _('Die folgenden Veranstaltungen können Sie betreten, ohne '
            .'sich im System registriert zu haben.') ?>
    </p>
    <p>
        <?= sprintf(_('In den %s blau markierten Veranstaltungen dürfen '
                     .'Sie nur lesen und Dokumente herunterladen.'),
                    '<span class="gruppe6">&nbsp;&nbsp;</span>') ?>
        <br>
        <?= sprintf(_('In den %s orange markierten Veranstaltungen '
                     .'können Sie sich zusätzlich mit eigenen Beiträgen '
                     .'im Forum beteiligen.'),
                    '<span class="gruppe2">&nbsp;&nbsp;</span>') ?>
    </p>
    <p>
        <?= _('In der rechten Spalte können Sie sehen, was in den '
              .'einzelnen Veranstaltungen an Inhalten vorhanden ist.') ?>
    </p>
</blockquote>

<? if (empty($seminars)): ?>
    <?= MessageBox::info(_('Es gibt keine Veranstaltungen, die einen freien Zugriff erlauben!'))?>
<? else: ?>
<table class="default">
    <colgroup>
        <col width="1em">
        <col>
        <col>
        <col>
        <col>
    </colgroup>
    <thead>
        <tr>
            <th>&nbsp;</th>
            <th><a href="<?= URLHelper::getLink('?sortby=Name') ?>"><?= _('Name') ?></a></th>
            <th><a href="<?= URLHelper::getLink('?sortby=status') ?>"><?=_ ('Veranstaltungstyp') ?></a></th>
            <th><a href="<?= URLHelper::getLink('?sortby=Institut') ?>"><?= _('Einrichtung') ?></a></th>
            <th><?= _('Inhalt') ?></th>
        </tr>
    </thead>
    <tbody>
    <? foreach ($seminars as $id => $values): ?>
        <tr>
            <td class="<?= $values['Schreibzugriff'] ? 'gruppe6' : 'gruppe2' ?>">&nbsp;</td>
            <td>
                <a href="<?= URLHelper::getLink('seminar_main.php?auswahl=' . $id) ?>">
                    <?= htmlReady($values['name']) ?>
                </a>
            </td>
            <td><?= htmlReady($GLOBALS['SEM_TYPE'][$values['status']]['name']) ?></td>
            <td>
                <a href="<?= URLHelper::getLink('dispatch.php/institute/overview?auswahl=' . $values['id']) ?>">
                    <?= htmlReady($values['Institut']) ?>
                </a>
            </td>
            <td style="white-space: nowrap;">
        <? foreach ($values['navigations'] as $navigation): ?>
            <? if (is_object($navigation) && $navigation->isVisible(true)): ?>
            <?
                $badge = '';
                if ($navigation->hasBadgeNumber()) {
                  $badge = ' class="badge" data-badge-number="' . intval($navigation->getBadgeNumber())  . '"';
                }
            ?>
                <a href="<?= URLHelper::getLink('seminar_main.php?auswahl='. $id . '&redirect_to=' . str_replace('?', '&', $navigation->getURL())) ?>"<?= $badge ?>>
                    <?= $navigation->getImageTag() ?>
                </a>
            <? else: ?>
                <?= Assets::img('blank.gif', ['width' => 16, 'height' => 16]) ?>
            <? endif; ?>
        <? endforeach; ?>
            </td>
        </tr>
    <? endforeach ?>
    </tbody>
</table>
<? endif; ?>
