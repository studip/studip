<?= strftime('%A, %x', $block->start) ?>

<?= sprintf(
    _('%s bis %s Uhr'),
    date('H:i', $block->start),
    date('H:i', $block->end)
) ?>

(<?= htmlReady($block->room) ?>
<? if ($block->course): ?>
    /
    <a href="<?= URLHelper::getLink('dispatch.php/course/details', ['sem_id' => $block->course_id]) ?>">
        <?= htmlReady($block->course->getFullName()) ?>
    </a>
<? endif; ?>
)

<? if ($block->note): ?>
<br>
<small>
    <?= htmlReady($block->note); ?>
</small>
<? endif; ?>
