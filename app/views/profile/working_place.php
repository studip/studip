<?php
$fields = [
    'raum'         => _('Raum'),
    'sprechzeiten' => _('Sprechzeit'),
    'telefon'      => _('Telefon'),
    'fax'          => _('Fax'),
];
?>

<dt><?= _('Wo ich arbeite:') ?></dt>
<dd>
    <ul>
    <? foreach ($institutes as $institute): ?>
        <li>
            <a href="<?= $controller->link_for('institute/overview', ['auswahl' => $institute['institut_id']]) ?>">
                <?= htmlReady($institute['institute_name']) ?>
            </a>

        <? foreach ($fields as $key => $label): ?>
            <? if ($institute[$key]): ?>
                <br>
                <strong><?= htmlReady($label) ?>:</strong>
                <?= htmlReady($institute[$key]) ?>
            <? endif; ?>
        <? endforeach; ?>

        <? if (!empty($institute['datafield'])): ?>
            <? foreach ($institute['datafield'] as $datafield): ?>
                <br>
                <strong><?= htmlReady($datafield['name']) ?>:</strong>
                <?= $datafield['value'] ?>
                <? if ($datafield['show_star']): ?>*<? endif; ?>
            <? endforeach; ?>
        <? endif; ?>

        <? if (!empty($institute['role'])): ?>
            <table cellpadding="0" cellspacing="0" border="0">
                <?= $institute['role'] ?>
            </table>
        <? endif; ?>
        </li>
    <? endforeach; ?>
    </ul>

</dd>
