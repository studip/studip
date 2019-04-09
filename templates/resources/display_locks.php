<? if (count($locks) > 0): ?>
    <table class="default" style="width: 50%;">
        <colgroup>
            <col width="2*">
            <col width="2*">
            <col width="1*">
        </colgroup>
        <tbody>
        <? foreach ($locks as $lock): ?>
            <tr>
            <? if ($_SESSION['resources_data']['lock_edits'][$lock['lock_id']]): ?>
                <!-- edit lock start time -->
                <td>
                    <label>
                        <?= _('Beginn') ?>
                        <input type="text" name="lock[begin][]"
                            value="<?= $lock['lock_begin'] ? date('d.m.Y H:i', $lock['lock_begin']) : '' ?>"
                            data-datetime-picker>
                    </label>
                </td>

                <!-- edit lock end time -->
                <td>
                    <label>
                        <?= _('Ende') ?>
                        <input type="text" name="lock[end][]"
                            value="<?= $lock['lock_end'] ? date('d.m.Y H:i', $lock['lock_end']) : '' ?>" 
                            data-datetime-picker>
                    </label>
                </td>

                <td style="text-align: right; vertical-align: bottom;">
                    <input type="hidden" name="lock_id[]" value="<?= $lock['lock_id'] ?>">

                    <?= Icon::create('accept', 'clickable', ['title' => _('Diesen Eintrag speichern')])->asInput(['name'=>'lock_sent','class'=>'text-top',]) ?>
                    <a href="<?= URLHelper::getLink('?kill_lock=' . $lock['lock_id']) ?>">
                        <?= Icon::create('trash', 'clickable')->asImg(['class' => 'text-top', 'title' => _('Diesen Eintrag löschen')]) ?>
                    </a>
                </td>
            <? else: ?>
                <td><?= date('d.m.Y H:i', $lock['lock_begin']) ?></td>
                <td><?= date('d.m.Y H:i', $lock['lock_end']) ?></td>
                <td style="text-align: right; vertical-align: bottom;">
                    <a href="<?= URLHelper::getLink('?edit_lock=' . $lock['lock_id']) ?>">
                        <?= Icon::create('edit', 'clickable', ['title' => _('Diesen Eintrag bearbeiten')])->asImg(['class' => 'text-top']) ?>
                    </a>
                    <a href="<?= URLHelper::getLink('?kill_lock=' . $lock['lock_id']) ?>">
                        <?= Icon::create('trash', 'clickable', ['title' => _('Diesen Eintrag löschen')])->asImg(['class' => 'text-top']) ?>
                    </a>
                </td>
            <? endif; ?>
            </tr>
        <? endforeach; ?>
        </tbody>
    </table>
<? endif; ?>
