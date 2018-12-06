<? foreach ($plugin_data as $label => $tabledata) : ?>
    <? if ($tabledata['table_content']) : ?>
        <h2 onclick="jQuery(this).next('table').toggle();" style="cursor: pointer;">
            <?= htmlReady($label) ?>, <?= sprintf(_('%u Einträge'), count($tabledata['table_content'])) ?>
            (<a href="<?= $controller->url_for("privacy/export2csv/{$tabledata['table_name']}/{$user_id}") ?>">
                <?= htmlReady($label) ?> CSV
            </a>)
        </h2>
        <table class="default" style="display: none;">
            <thead>
                <tr>
                <? foreach (array_keys($tabledata['table_content'][0]) as $caption) : ?>
                    <th><?= htmlReady($caption) ?></th>
                <? endforeach; ?>
                </tr>
            </thead>
            <tbody>
            <? foreach ($tabledata['table_content'] as $row) : ?>
                <tr>
                <? foreach ($row as $key => $value): ?>
                    <td>
                    <? if ($tabledata['table_name'] === 'log_events' && $key === "readable_entry"): ?>
                        <?= $value ?>
                    <? else: ?>
                        <?= htmlReady($value) ?>
                    <? endif; ?>
                    </td>
                <? endforeach; ?>
                </tr>
            <? endforeach; ?>
            </tbody>
        </table>
    <? endif; ?>
<? endforeach; ?>
