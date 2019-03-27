<?= $controller->jsUrl() ?>
<table class="default collapsable">
    <colgroup>
        <col>
        <col style="width: 20px;">
    </colgroup>
    <thead>
        <tr class="sortable">
            <?= $controller->renderSortLink('module/institute/index', _('Einrichtung'), 'name') ?>
            <?= $controller->renderSortLink('module/institute/index', _('Module'), 'count_objects', ['style' => 'text-align: center;']) ?>
        </tr>
    </thead>
    <? foreach ($institute as $institut) : ?>
        <? if (!$institut->id) continue; ?>
        <tbody class="<?= ($institut->count_objects ? '' : 'empty') ?> <?= ($inst_id == $institut->id ? 'not-collapsed' : 'collapsed') ?>">
            <tr class="header-row" id="institut_<?= $institut->id ?>">
                <td class="toggle-indicator">
                    <? if ($institut->count_objects) : ?>
                        <a class="mvv-load-in-new-row"
                           href="<?= $controller->url_for('/details', ['institut_id' => $institut->id]) ?>">
                            <?= htmlReady($institut->getDisplayName()) ?>
                        </a>
                    <? else : ?>
                        <?= htmlReady($institut->getDisplayName()) ?>
                    <? endif; ?>
                </td>
                <td style="text-align: center;" class="dont-hide"><?= $institut->count_objects ?></td>
            </tr>
            <? if ($institut->id && $inst_id == $institut->id) : ?>
                <tr class="loaded-details nohover">
                    <?= $this->render_partial('module/institute/details',
                        ['institut_id' => $institut->id]
                    ) ?>
                </tr>
            <? endif; ?>
        </tbody>
    <? endforeach; ?>
</table>

