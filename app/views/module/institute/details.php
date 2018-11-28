<td colspan="2">
    <table class="default collapsable">
        <colgroup>
            <col style="width: 10%;">
            <col>
            <col span="2" style="width: 5%;">
            <col span="2" style="width: 150px;">
        </colgroup>
        <thead>
            <tr>
                <th><?= _('Modul') ?></th>
                <th><?= _('Modulcode') ?></th>
                <th><?= _('Fassung') ?></th>
                <th><?= _('Modulteile') ?></th>
                <th style="text-align: center;">
                    <?= _('Ausgabesprachen') ?>
                </th>
                <th> </th>
            </tr>
        </thead>
        <?= $this->render_partial('module/module/module') ?>
    </table>
</td>