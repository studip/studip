<? foreach ($tree[$node] as $current) : ?>
<li>
    <? if ($current['class'] != 'Modulteil' && $current['id'] != 'root') : ?>
        <input id="<?= htmlReady($current['id'] . $id_sfx->c) ?>" type="checkbox"<?= $current['class'] != 'StgteilabschnittModul' ? 'checked' : ''?>>
    <? endif; ?>
        <label for="<?= htmlReady($current['id'] . $id_sfx->c++) ?>"></label>
    <? if ($current['class'] == 'StgteilabschnittModul') : ?>
        <a data-dialog title="<?= htmlReady($current['name']) ?>" href="<?= URLHelper::getLink('dispatch.php/shared/modul/overview/' . $current['id']) ?>">
            <?= htmlReady($current['name']) ?>
        </a>
        <a data-dialog title="<?= htmlReady($current['name']) ?>" href="<?= URLHelper::getLink('dispatch.php/shared/modul/description/' . $current['id']) ?>">
        <?= Icon::create('log', 'clickable', ['title' => _('Modulbeschreibung')]); ?>
        </a>
    <? else : ?>
        <?= htmlReady($current['name']) ?>
    <? endif; ?>
    <? if ($current['class'] != 'Modulteil') : ?>
        <ul>
            <?= $this->render_partial('shared/mvv_tree.php', ['tree' => $tree, 'node' => $current['id']]) ?>
        </ul>
    <? endif; ?>
</li>
<? endforeach; ?>
