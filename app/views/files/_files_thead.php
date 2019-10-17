    <colgroup>
        <col width="30px">
        <col width="20px">
        <col>
        <col width="100px" class="responsive-hidden">
        <col width="150px" class="responsive-hidden">
        <col width="120px" class="responsive-hidden">
        <col width="80px">
    </colgroup>
    <thead>
        <tr class="sortable">
            <th data-sort="false">
                <input type="checkbox"
                       class="studip-checkbox"
                       data-proxyfor="table.documents tbody :checkbox"
                       data-activates="table.documents tfoot .multibuttons .button"
                       id="all_files_checkbox">
                <label for="all_files_checkbox"></label>
            </th>
            <th data-sort="htmldata"><?= _('Typ') ?></th>
            <th data-sort="text"><?= _('Name') ?></th>
            <th data-sort="htmldata" class="responsive-hidden"><?= _('Größe') ?></th>
        <? if ($show_downloads) : ?>
            <th data-sort="htmldata" class="responsive-hidden"><?= _('Downloads') ?></th>
        <? endif ?>
            <th data-sort="text" class="responsive-hidden"><?= _('Autor/-in') ?></th>
            <th data-sort="htmldata" class="responsive-hidden"><?= _('Datum') ?></th>
            <th data-sort="false"><?= _('Aktionen') ?></th>
        </tr>
    </thead>
