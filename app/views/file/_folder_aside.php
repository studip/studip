<aside id="folder_aside">
    <div class="folder-icon"><?= $folder->getIcon('info') ?></div>

    <table class="default nohover">
        <caption><?= htmlReady($folder->name) ?></caption>
        <tbody>
            <? if ($folder->mkdate) : ?>
                <tr>
                    <td><?= _('Erstellt') ?></td>
                    <td><?= date('d.m.Y H:i', $folder->mkdate) ?></td>
                </tr>
            <? endif ?>
            <? if ($folder->chdate) : ?>
                <tr>
                    <td><?= _('Geändert') ?></td>
                    <td><?= date('d.m.Y H:i', $folder->chdate) ?></td>
                </tr>
            <? endif ?>
            <tr>
                <td><?= _('Besitzer/-in') ?></td>
                <td>
                <? if ($folder->owner): ?>
                    <?= htmlReady($folder->owner->getFullName()) ?>
                <? endif ?>
                </td>
            </tr>
            <tr>
                <td><?= _('Anz. Dateien') ?></td>
                <td><?= number_format($folder_file_amount, 0, ',', '.') ?></td>
            </tr>
            <tr>
                <td><?= _('Größe') ?></td>
                <td><?= relsize($folder_size, false) ?></td>
            </tr>
        </tbody>
    </table>
</aside>
