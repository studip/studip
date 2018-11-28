<? foreach ($data as $row):
    if (!$row['groups']) continue;
?>
    <optgroup label="<?= htmlReady(mb_substr($row['Name'], 0, 70)) ?>">
        <?= $this->render_partial('settings/statusgruppen/_options', ['data' => $row['groups']]) ?>
    </optgroup>
<? if ($row['sub']): ?>
    <?= $this->render_partial('settings/statusgruppen/_optgroup', ['data' => $row['sub']]) ?>
<? endif; ?>
<? endforeach; ?>
