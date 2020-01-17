<td colspan="2">
    <table class="default collapsable">
        <thead>
            <tr>
                <th style="width: 7%;"><?= _('Modulcode') ?></th>
                <th><?= _('Modul') ?></th>
                <th style="width: 5%;"><?= _('Fassung') ?></th>
                <th style="width: 5%;"><?= _('Modulteile') ?></th>
                <th style="text-align: center; width: 150px;">
                    <?= _('Ausgabesprachen') ?>
                </th>
                <th style="width: 5%; text-align: right;"><?= _('Aktionen') ?></th>
            </tr>
        </thead>
        <?= $this->render_partial('module/module/module') ?>
    </table>
</td>