<div class="<?= $base_class ?>-widget">
<? if ($title): ?>
    <div class="<?= $base_class ?>-widget-header">
        <?= htmlReady($title) ?>
    </div>
<? endif; ?>
    <div class="<?= $base_class ?>-widget-content">
        <?= $content_for_layout ?>
    </div>
</div>
