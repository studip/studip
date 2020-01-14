<thead>
    <tr>
        <th><?= _('Raumname') ?></th>
        <th><?= _('Sitzplätze') ?></th>
        <? if ($additional_properties): ?>
            <? foreach ($additional_properties as $display_name): ?>
                <th>
                    <?= htmlReady($display_name) ?>
                </th>
            <? endforeach ?>
        <? endif ?>
        <? if ($additional_columns): ?>
            <? foreach ($additional_columns as $column_name): ?>
                <th>
                    <?= htmlReady($column_name) ?>
                </th>
            <? endforeach ?>
        <? endif ?>
        <th class="actions"><?= _('Aktionen') ?></th>
    </tr>
</thead>
