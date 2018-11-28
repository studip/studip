<?php
$groups = Statusgruppen::findBySeminar_id(Request::get('cid'));
?>
<label>
    <?= _('Wählen sie eine zugehörige Gruppe aus') ?>
    <select name="group">
    <? if (count($groups) === 0): ?>
        <option value="">
            <?= _('Es existiert keine Gruppe in dieser Veranstaltung') ?>
        </option>
    <? endif; ?>
    <? foreach ($groups as $one_group): ?>
        <option <?=(@$group->id === $one_group->id ? 'selected' : '')?> value="<?= htmlReady($one_group->id) ?>">
            <?= htmlReady($one_group->name) ?>
        </option>
    <? endforeach; ?>
    </select>
</label>
