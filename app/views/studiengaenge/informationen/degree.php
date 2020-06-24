<table class="default nohover collapsable">
    <colgroup>
        <col width="70%">
        <col width="29%">
        <col width="1%">
    </colgroup>
    <thead>
        <tr>
            <th><?= _('Abschluss') ?></th>
            <th><?= _('Studierende') ?></th>
            <th class="actions"><?= _('Aktionen') ?></th>
        </tr>
    </thead>
    <? foreach ($degree as $key => $deg) : ?>
        <? if (($studycount = Studiengaenge_InformationenController::getStudyCount($deg->abschluss_id)) > 0  ) : ?>
    <tbody class="collapsed">
            <tr class="table-header header-row">
                <td class="toggle-indicator">
                    <a id="<?= $deg->abschluss_id?>"  class="mvv-load-in-new-row"
                       href="<?= $controller->url_for('/showstudycourse', $deg->abschluss_id, $key+1)?>"
                       onclick="icon_toggle(this)">

                        <?= htmlReady($deg->name) ?>
                    </a>
                </td>
                <td>
                    <?= $studycount ?>
                </td>
                <td class="dont-hide actions">
                    <? if ($GLOBALS['perm']->have_perm("root", $GLOBALS['user']->id)) : ?>

                        <a href="<?= $controller->url_for('/messagehelper',
                                ['abschluss_id' => $deg->abschluss_id]) ?>" data-dialog >

                            <?= Icon::create('mail', Icon::ROLE_CLICKABLE,
                                ['title' => htmlReady(sprintf(_('Alle Studierenden mit dem Studienabschluss %s benachrichtigen.'),
                                        $deg->name))]) ?>
                        </a>
                    <? endif; ?>
                </td>
            </tr>
    </tbody>
        <? endif; ?>
    <? endforeach; ?>
</table>
