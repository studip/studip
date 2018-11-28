<?php
    $selected   = !$schedule->isNew() && $schedule->task_id === $task->task_id;
    $parameters = $schedule->parameters;
?>

<h3><?= _('Parameter') ?></h3>
<ul class="clean">
<? foreach ($task->parameters as $key => $data): ?>
    <li class="<? if ($data['status'] === 'mandatory') echo 'required'; ?> parameter">
    <? if ($data['type'] === 'boolean'): ?>
        <input type="hidden" name="parameters[<?= $task->task_id ?>][<?= htmlReady($key) ?>]" value="0">
        <label>
            <input type="checkbox" name="parameters[<?= $task->task_id ?>][<?= htmlReady($key) ?>]" value="1"
                   id="parameter-<?= htmlReady($key) ?>"
                   <? if ($selected ? $parameters[$key] : $data['default']) echo 'checked'; ?>>
            <?= htmlReady($data['description']) ?>
        </label>
    <? else: ?>
        <label for="parameter-<?= htmlReady($key) ?>">
            <?= htmlReady($data['description']) ?>
        <? if ($data['status'] !== 'mandatory'): ?>
            [<?= _('optional') ?>]
        <? endif; ?>

    <? endif; ?>
    <? if ($data['type'] === 'string'): ?>
        <input type="text" name="parameters[<?= $task->task_id ?>][<?= htmlReady($key) ?>]"
               id="parameter-<?= htmlReady($key) ?>"
               value="<?= htmlReady($selected ? $parameters[$key] : ($data['default'] ?: '')) ?>"
               placeholder="<?= $data['default'] ?: '' ?>"
               <? if ($data['status'] === 'mandatory') echo 'class="required"'; ?>>
    <? elseif ($data['type'] === 'text'): ?>
        <textarea name="parameters[<?= $task->task_id ?>][<?= htmlReady($key) ?>]"
                  id="parameter-<?= htmlReady($key) ?>"
                  placeholder="<?= $data['default'] ?: '' ?>"
                  <? if ($data['status'] === 'mandatory') echo 'class="required"'; ?>
        ><?= htmlReady($selected ? $parameters[$key] : ($data['default'] ?: '')); ?></textarea>
    <? elseif ($data['type'] === 'integer'): ?>
        <input type="number" name="parameters[<?= $task->task_id ?>][<?= htmlReady($key) ?>]"
               id="parameter-<?= htmlReady($key) ?>"
               placeholder="<?= $data['default'] ?: '' ?>"
               value="<?= (int)($selected ? $parameters[$key] : ($data['default'] ?: 0)) ?>"
               <? if ($data['status'] === 'mandatory') echo 'class="required"'; ?>>
    <? elseif ($data['type'] === 'select'): ?>
        <select name="parameters[<?= $task->task_id ?>][<?= htmlReady($key) ?>]">
        <? if ($data['status'] === 'optional'): ?>
            <option value=""><?= _('Bitte wählen Sie einen Wert aus') ?></option>
        <? endif; ?>
        <? foreach ($data['values'] as $k => $l): ?>
            <option value="<?= htmlReady($k) ?>"
                    <? if (($parameters[$key] ?: $data['default'] ?: null) === $k) echo 'selected'; ?>>
                <?= htmlReady($l) ?>
            </option>
        <? endforeach; ?>
        </select>
    <? endif; ?>
        </label>
    </li>
<? endforeach; ?>
</ul>
