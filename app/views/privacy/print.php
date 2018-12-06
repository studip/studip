<h1><?= sprintf(_('Personenbezogene Daten von %s'), htmlReady($user_fullname)); ?></h1>
<? foreach ($plugin_data as $label => $tabledata) : ?>
    <? if ($tabledata['table_content']) : ?>
        <h3><?= htmlReady($label) ?></h3>
        <? foreach ($tabledata['table_content'] as $row) : ?>
            <table border="1">
                <tbody>
                <? foreach ($row as $key => $value) : ?>
                    <tr>
                        <th><?= htmlReady($key) ?></th>
                        <td>
                        <? if ($tabledata['table_name'] === 'log_events' && $key === 'readable_entry') : ?>
                            <?= $value ?>
                        <? else: ?>
                            <?= htmlReady($value) ?>
                        <? endif; ?>
                        </td>
                    </tr>
                <? endforeach; ?>
                </tbody>
            </table>
            <br>
        <? endforeach; ?>
    <? endif; ?>
<? endforeach; ?>
