<?php
// Load content before displaying the widget. This is neccessary so that
// widgets may alter the otherwise static title in #getTitle()/#getName().
$content = '';
if ($mode === 'admin') {
    $content = _('Bearbeitungsmodus');
} elseif ($widget->enabled) {
    $content = $widget->getContent($container->range, $container->scope);
}
?>
<header class="widget-header">
    <h2 class="widget-title">
        <?= htmlReady($widget->getTitle()) ?>
        <? if (!$widget->enabled) echo '(' . _('Deaktiviert') . ')'; ?>
    </h2>
</header>
<article class="widget-content"><?= $content ?></article>
