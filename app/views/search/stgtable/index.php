<table class="default">
    <colgroup>
        <col>
        <? for ($i = count($kategorien); $i > 0; $i--) : ?>
        <col style="text-align: center;">
        <? endfor; ?>
    </colgroup>
    <thead>
        <tr>
            <th><?= _('Studiengänge') ?></th>
            <? foreach ($kategorien as $kategorie): ?>
                <th style="text-align: center;"><?= htmlReady($kategorie->name) ?></th>
            <? endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <? foreach ($stgs as $name => $stg): ?>
            <tr>
                <td><?= htmlReady($name) ?></td>
                <? foreach ($kategorien as $kat_id => $kategorie): ?>
                    <? if ($stg[$kat_id]) : ?>
                <td style="text-align: center;">
                    <a href="<?= $controller->link_for('/studiengang', $stg[$kat_id]) ?>"><?= Icon::create('info-circle-full', 'clickable', ['title' => _('Studiengang anzeigen')])->asImg(); ?></a>
                </td>
                    <? else : ?>
                        <td></td>
                    <? endif; ?>
                <? endforeach; ?>
            </tr>
        <? endforeach; ?>
    </tbody>
</table>