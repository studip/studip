<div class="messagebox messagebox_<?= $class ?> <? if (count($details) > 0 && $close_details): ?>details_hidden<? endif; ?>">
    <div class="messagebox_buttons">
    <? if (count($details) > 0 && $close_details) : ?>
        <a class="details" href="#" title="<?=_('Detailanzeige umschalten')?>">
            <span><?= _('Detailanzeige umschalten') ?></span>
        </a>
    <? endif ?>
    <? if (!$hide_close): ?>
        <a class="close" href="#" title="<?= _('Nachrichtenbox schließen') ?>">
            <span><?= _('Nachrichtenbox schließen') ?></span>
        </a>
    <? endif; ?>
    </div>
    <?= $message ?>
<? if (count($details) > 0) : ?>
    <div class="messagebox_details">
        <ul>
        <? foreach ($details as $li) : ?>
            <li><?= $li ?></li>
        <? endforeach ?>
        </ul>
    </div>
<? endif ?>
</div>
