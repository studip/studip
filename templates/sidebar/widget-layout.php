<div class="<?= $base_class ?>-widget <? if ($layout_css_classes && is_array($layout_css_classes)) echo htmlReady(implode(' ', $layout_css_classes)); ?>"
    <? if ($id) printf('id="%s"', htmlReady($id)) ?>
    <? if ($style) printf('style="%s"', $style) ?>>
<? if ($title): ?>
    <div class="<?= $base_class ?>-widget-header">
    <? if ($extra): ?>
        <div class="<?= $base_class ?>-widget-extra"><?= $extra ?></div>
    <? endif; ?>
        <?= htmlReady($title) ?>
    </div>
<? endif; ?>
    <div class="<?= $base_class ?>-widget-content">
        <?= $content_for_layout ?>
    </div>
</div>
