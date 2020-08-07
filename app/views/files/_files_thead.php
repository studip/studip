<colgroup>
    <? if ($show_bulk_checkboxes) : ?>
        <col width="30px" data-filter-ignore>
    <? endif ?>
        <col width="20px" data-filter-ignore>
        <col>
        <col width="100px" class="responsive-hidden" data-filter-ignore>
    <? if ($show_downloads) : ?>
        <col width="100px" class="responsive-hidden" data-filter-ignore>
    <? endif; ?>
        <col width="150px" class="responsive-hidden">
        <col width="120px" class="responsive-hidden" data-filter-ignore>
        <col width="80px" data-filter-ignore>
    </colgroup>
    <thead>
        <tr class="sortable">
            <? if ($show_bulk_checkboxes) : ?>
                <th data-sort="false">
                    <input type="checkbox"
                           class="studip-checkbox"
                           <?= $table_id
                             ? 'data-proxyfor="table.documents[data-table_id=\'' . htmlReady($table_id) . '\'] tbody :checkbox"'
                             : 'data-proxyfor="table.documents tbody :checkbox"'
                           ?>
                           <?= $table_id
                             ? 'data-activates="table.documents[data-table_id=\'' . htmlReady($table_id) . '\'] tfoot .multibuttons .button"'
                             : 'data-activates="table.documents tfoot .multibuttons .button"'
                           ?>
                           id="all_files_checkbox_<?= htmlReady($table_id) ?>">
                    <label for="all_files_checkbox_<?= htmlReady($table_id) ?>"></label>
                </th>
            <? endif ?>
            <th data-sort="htmldata"><?= _('Typ') ?></th>
            <th data-sort="text"><?= _('Name') ?></th>
            <th data-sort="htmldata" class="responsive-hidden"><?= _('Größe') ?></th>
        <? if ($show_downloads) : ?>
            <th data-sort="htmldata" class="responsive-hidden"><?= _('Downloads') ?></th>
        <? endif ?>
            <th data-sort="text" class="responsive-hidden"><?= _('Autor/-in') ?></th>
            <th data-sort="htmldata" class="responsive-hidden"><?= _('Datum') ?></th>
            <? if ($topFolder) : ?>
                <? foreach ($topFolder->getAdditionalColumns() as $index => $column_name) : ?>
                    <th data-sort="htmldata" class="responsive-hidden"><?=htmlReady($column_name) ?></th>
                <? endforeach ?>
            <? endif ?>
            <th data-sort="false"><?= _('Aktionen') ?></th>
        </tr>
    </thead>
