<table class="default collapsable">
    <thead>
        <tr class="sortable">
            <?= $controller->renderSortLink('/index', _('Abschluss-Kategorie'), 'name') ?>
            <?= $controller->renderSortLink('/index', _('Studiengänge'), 'count_studiengaenge', ['style' => 'text-align: center; width: 10%;']) ?>
            <th style="width: 5%; text-align: right;"><?= _('Aktionen') ?></th>
        </tr>
    </thead>
    <? foreach ($kategorien as $kategorie) : ?>
        <?php
        // skip unknown Abschluesse
        if (is_null($kategorie->name)) {
            continue;
        }
        ?>
        <tbody class="<?= ($kategorie->count_studiengaenge ? '' : 'empty') ?> <?= ($kategorie_id === $kategorie->id ? 'not-collapsed' : 'collapsed') ?>">
            <tr class="header-row" id="kategorie_<?= $kategorie->id ?>">
                <td class="toggle-indicator">
                    <? if (is_null($kategorie->name) && $kategorie->count_studiengaenge) : ?>
                        <a class="mvv-load-in-new-row"
                           href="<?= $controller->url_for('/details/' . $kategorie->id) ?>">
                            <?= _('Keiner Abschluss-Kategorie zugeordnet') ?>
                        </a>
                    <? else : ?>
                        <? if ($kategorie->count_studiengaenge) : ?>
                            <a class="mvv-load-in-new-row"
                               href="<?= $controller->url_for('/details/' . $kategorie->id) ?>">
                                <?= htmlReady($kategorie->getDisplayName()) ?>
                            </a>
                        <? else : ?>
                            <?= htmlReady($kategorie->getDisplayName()) ?>
                        <? endif; ?>
                    <? endif; ?>
                </td>
                <td style="text-align: center;" class="dont-hide"><?= $kategorie->count_studiengaenge ?></td>
                <td></td>
            </tr>
            <? if ($kategorie_id == $kategorie->id) : ?>
                <tr class="loaded-details nohover">
                    <?= $this->render_partial('studiengaenge/studiengaenge/details') ?>
                </tr>
            <? endif; ?>
        </tbody>
    <? endforeach; ?>
</table>
