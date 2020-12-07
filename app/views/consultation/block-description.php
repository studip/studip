<?= strftime('%A, %x', $block->start) ?>

<?= sprintf(
    _('%s bis %s Uhr'),
    date('H:i', $block->start),
    date('H:i', $block->end)
) ?>

<? if ($block->teacher): ?>
/
<a href="<?= URLHelper::getLink('dispatch.php/profile', ['username' => $block->teacher->username]) ?>">
    <?= htmlReady($block->teacher->getFullName()) ?>
</a>
<? endif; ?>

(<?= formatLinks($block->room) ?>)

<? if ($block->show_participants): ?>
    - <?= _('öffentlich sichtbar') ?>
    <?= tooltipIcon(_('Die Namen der buchenden Person sind sichtbar')) ?>
<? endif; ?>

<? if ($block->note): ?>
<br>
<small>
    <?= formatLinks($block->note); ?>
</small>
<? endif; ?>
