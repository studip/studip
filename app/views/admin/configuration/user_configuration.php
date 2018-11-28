<table class="default">
    <caption>
        <?= htmlReady($title) ?>
    <? if (!empty($subtitle)): ?>
        <br>
        <small><?= htmlReady($subtitle) ?></small>
    <? endif; ?>
    </caption>
    <?= $this->render_partial('admin/configuration/table-header.php') ?>
    <tbody>
    <? foreach ($configs as $config): ?>
        <?= $this->render_partial('admin/configuration/table-row.php', $config) ?>
    <? endforeach; ?>
    </tbody>
</table>
