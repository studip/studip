<? if (count($actions) > 0): ?>
<ul class="widget-actions">
<? foreach ($actions as $name => $action): ?>
    <li>
        <a class="widget-action <?= $action->getAttributes()['class'] ?: '' ?>" title="<?= htmlReady($action->getLabel()) ?>"
           data-action="<?= htmlReady($name) ?>" <?= arrayToHtmlAttributes(array_diff_key($action->getAttributes(), ['class' => ''])) ?>
           <? if ($action->getAdminMode()) echo 'data-admin'; ?>>
        <? if ($action->getIcon()): ?>
            <?= $action->getIcon()->asImg(tooltip2($action->getLabel())) ?>
        <? else: ?>
            <?= htmlReady($action->getLabel()) ?>
        <? endif; ?>
        </a>
    </li>
<? endforeach; ?>
</ul>
<? endif; ?>
