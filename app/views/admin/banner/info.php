<table class="default">
    <tbody>
        <tr>
            <td rowspan="9" colspan="2" style="text-align: center;">
            <? if ($banner['banner_path']): ?>
                <?= $banner->toImg() ?>
            <? else: ?>
                <?= _('noch kein Bild hochgeladen') ?>
            <? endif; ?>
            </td>
        </tr>
        <tr>
            <td><?= _("Beschreibung:") ?></td>
            <td>
                <input type="text" readonly
                       value="<?= htmlReady($banner['description']) ?>"
                       size="40" maxlen="254">
            </td>
        </tr>
        <tr>
            <td><?= _('Alternativtext:') ?></td>
            <td>
                <input type="text" readonly
                       value="<?= htmlReady($banner['alttext']) ?>"
                       size="40" maxlen="254">
            </td>
        </tr>
        <tr>
            <td><?= _('Verweis-Typ:') ?></td>
            <td>
                 <select disabled>
                 <? foreach ($target_types as $key => $label): ?>
                    <option value="<?= $key ?>" <? if ($banner['target_type'] == $key) echo 'selected'; ?>>
                        <?= $label ?>
                    </option>
                <? endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <td><?= _('Verweis-Ziel:') ?></td>
            <td>
                <input type="text" readonly
                       value="<?= htmlReady($this->edit['target']) ?>"
                       size="40" maxlen="254">
            </td>
        </tr>
        <tr>
            <td><?= _('Anzeigen ab:')?></td>
            <td>
                <?= $this->render_partial('admin/banner/datetime-picker', [
                        'prefix'    => 'start_',
                        'timestamp' => $banner['startdate'],
                        'disabled'  => true]) ?>
            </td>
        </tr>
        <tr>
            <td><?= _('Anzeigen bis:') ?></td>
            <td>
                <?= $this->render_partial('admin/banner/datetime-picker', [
                        'prefix'    => 'end_',
                        'timestamp' => $banner['enddate'],
                        'disabled'  => true]) ?>
            </td>
        </tr>
        <tr>
            <td><?= _('Priorität:') ?></td>
            <td>
                <select disabled>
                <? foreach ($priorities as $key => $label): ?>
                    <option value="<?= $key ?>" <? if ($banner['priority'] == $key) echo 'selected'; ?>>
                        <?= $label ?>
                    </option>
                <? endforeach; ?>
                </select>
            </td>
        </tr>
    </tbody>
</table>

