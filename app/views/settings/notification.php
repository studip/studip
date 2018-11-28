<? use Studip\Button, Studip\LinkButton; ?>
<form method="post" action="<?= $controller->url_for('settings/notification/store') ?>" class="default">
    <?= CSRFProtection::tokenTag() ?>
    <input type="hidden" name="studip_ticket" value="<?= get_ticket() ?>">

    <table class="default" id="settings-notifications">
        <colgroup>
            <col width="7px">
            <col width="100%">
            <? for ($i = 0; $i < count($modules); $i += 1): ?>
                <col width="20px">
            <? endfor; ?>
        </colgroup>
        <thead>
            <tr>
                <th colspan="2"><?= _('Veranstaltung') ?></th>
                <? foreach ($modules as $name => $data): ?>
                    <th>
                        <?= Icon::create($controller->module_icon($name), 'info', ['title' => $data['name']])
                                ->asImg(['class' => 'middle']) ?>
                    </th>
                <? endforeach; ?>
                <th><?= _('Alle') ?></th>
            </tr>
            <tr>
                <td colspan="2">
                    <?= _('Benachrichtigung für unten aufgeführte Veranstaltungen:') ?>
                </td>
                <? for ($i = 0; $i < count($modules); $i += 1): ?>
                    <td>
                        <input type="checkbox" name="all[columns][]" value="<?= $i ?>"
                                <? if (!empty($checked) && count(array_filter($checked, function ($item) use ($i) { return $item[$i]; })) == count($checked)) echo 'checked'; ?>>
                    </td>
                <? endfor; ?>
                <td>
                    <input type="checkbox" name="all[all]" value="all"
                            <? if (!empty($checked) && count(array_filter($checked, function ($item) { return $item['all']; })) == count($checked)) echo 'checked'; ?>>

                </td>
            </tr>
        </thead>
        <? foreach ($groups as $id => $members): ?>
            <tbody>
                <? if ($group_field !== 'not_grouped'): ?>
                    <tr>
                        <th colspan="<?= 3 + count($modules) ?>">
                            <? if (isset($open[$id])): ?>
                            <a class="tree" style="font-weight:bold" name="<?= $id ?>"
                               href="<?= $controller->url_for('settings/notification/close', $id) ?>#<?= $id ?>"
                                    <?= tooltip(_('Gruppierung schließen'), true) ?>>
                                <?= Icon::create('arr_1down', 'clickable')->asImg() ?>
                                <? else: ?>
                                <a class="tree" name="<?= $id ?>"
                                   href="<?= $controller->url_for('settings/notification/open', $id) ?>#<?= $id ?>"
                                        <?= tooltip(_('Gruppierung öffnen'), true) ?>>
                                    <?= Icon::create('arr_1right', 'clickable')->asImg() ?>
                                    <? endif; ?>
                                    <?= htmlReady(my_substr(implode(' &gt; ', (array)$group_names[$id]), 0, 70)) ?>
                                </a>
                        </th>
                    </tr>
                <? endif; ?>
                <? if ($id === 'not_grouped' || isset($open[$id])): ?>
                    <? foreach ($members as $member): ?>
                        <tr>
                            <td class="gruppe<?= $seminars[$member['seminar_id']]['gruppe'] ?>">&nbsp;</td>
                            <td>
                                <a href="<?= URLHelper::getLink('seminar_main.php', ['auswahl' => $member['seminar_id']]) ?>">
                                    <?= Config::get()->IMPORTANT_SEMNUMBER ? htmlReady($seminars[$member['seminar_id']]['sem_nr']) : '' ?>
                                    <?= htmlReady(my_substr($seminars[$member['seminar_id']]['name'], 0, 70)) ?>
                                </a>
                                <? if (!$seminars[$member['seminar_id']]['visible']): ?>
                                    <?= _('(versteckt)') ?>
                                <? endif; ?>
                                <input type="hidden" name="m_checked[<?= $member['seminar_id'] ?>][33]" value="0">
                            </td>
                            <? foreach (array_values($modules) as $index => $data): ?>
                                <td>
                                    <input type="checkbox" name="m_checked[<?= $member['seminar_id'] ?>][<?= $index ?>]"
                                           value="<?= pow(2, $data['id']) ?>"
                                            <? if ($checked[$member['seminar_id']][$index]) echo 'checked'; ?>>
                                </td>
                            <? endforeach; ?>
                            <td>
                                <input type="checkbox" name="all[rows][]" value="<?= $member['seminar_id'] ?>"
                                        <? if (isset($checked[$member['seminar_id']]) && count(array_filter($checked[$member['seminar_id']])) == count($modules) + 1) echo 'checked'; ?>>
                            </td>
                        </tr>
                    <? endforeach; ?>
                <? endif; ?>
            </tbody>
        <? endforeach; ?>
        </table>
        <footer>
            <?= Button::create(_('Übernehmen'), 'store', ['title' => _('Änderungen übernehmen')]) ?>
            <?= LinkButton::create(_('Abbrechen'), $controller->url_for('settings/notification')) ?>
        </footer>
</form>
