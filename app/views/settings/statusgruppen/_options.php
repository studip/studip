<? if (!$data) return; ?>

<? foreach ($data as $id => $row): ?>
    <option value="<?= $id ?>" <? if ($row['user_there']) echo 'disabled'; ?>>
    <? for ($i = 1; $i <= $level; $i++): ?>
        &nbsp;&nbsp;
    <? endfor; ?>
        <?= mb_substr($row['role']->getName(), 0, 70) ?>
    </option>
<? if ($row['child']): ?>
    <?= $this->render_partial('settings/statusgruppen/_options', [
        'data'  => $row['child'],
        'level' => $level + 1,
    ]) ?>
<? endif; ?>

<? endforeach; ?>
