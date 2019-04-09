<table class="default">
    <caption>
        <?= sprintf(_('Veranstaltungen mit regelmäßigen Zeiten am %s, %s Uhr'), htmlReady($day), htmlReady($start) .' - '. htmlReady($end)) ?>
    </caption>
    <colgroup>
        <col width="15%">
        <col width="85%">
    </colgroup>
    <thead>
        <tr>
            <th><?= _('Nummer') ?></th>
            <th><?= _('Name') ?></th>
        </tr>
    </thead>
    <tbody>
        <? foreach ($seminars as $seminar) : ?>
            <tr class="<?= TextHelper::cycle('table_row_odd', 'table_row_even')?>">
                <td><?= htmlReady($seminar->getNumber()) ?></td>
                <td>
                    <a href="<?= URLHelper::getLink('dispatch.php/course/details/', ['sem_id' => $seminar->getId()]) ?>">
                        <?= Icon::create('link-intern', 'clickable')->asImg() ?>
                        <?= htmlReady($seminar->getName()) ?>
                    </a>
                </td>
            </tr>
        <? endforeach ?>
    </tbody>
</table>
<br>
