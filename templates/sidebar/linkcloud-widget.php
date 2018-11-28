<ul class="sidebar-widget-cloud">
<? foreach ($elements as $element): ?>
    <li><?= $element->render() ?></li>
<? endforeach; ?>
</ul>