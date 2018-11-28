<ul class="sidebar-widget-links">
<? foreach ($elements as $index => $element): ?>
    <li id="<?= htmlReady($index) ?>"><?= $element->render() ?></li>
<? endforeach; ?>
</ul>