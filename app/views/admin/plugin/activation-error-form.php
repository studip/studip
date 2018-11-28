<?php
$aggregated = array_sum($memory);
if (!$aggregated) {
    $question = _('Markierte Plugins dennoch deaktivieren');
} elseif ($aggregated === count($memory)) {
    $question = _('Markierte Plugins dennoch aktivieren');
} else {
    $question = _('Markierte Plugins dennoch aktivieren bzw. deaktivieren');
}
?>

<form action="<?= $controller->url_for('admin/plugin/save') ?>" method="post">
    <input type="hidden" name="studip_ticket" value="<?= get_ticket() ?>">
    <input type="hidden" name="force" value="1">

    <?= _('Die folgenden Fehler sind aufgetreten:') ?>
    <div class="messagebox_details">
        <ul>
        <? foreach ($errors as $plugin_id => $error): ?>
            <li>
                <label>
                    <input type="checkbox" name="enabled_<?= $plugin_id ?>"
                           value="<?= (int) $memory[$plugin_id] ?>">
                    <?= htmlReady($error) ?>
                </label>
            </li>
        <? endforeach; ?>
        </ul>
    </div>

    <?= Studip\Button::create($question) ?>
</form>
