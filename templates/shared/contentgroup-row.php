
<?php
$attributes = function (array $attributes) {
    $result = [];
    foreach ($attributes as $key => $value) {
        if ($value === null) {
            $result[] = htmlReady($key);
        } else {
            $result[] = sprintf('%s="%s"', htmlReady($key), htmlReady($value));
        }
    }
    return implode(' ', $result);
};
?>
<nav class="action-menu">
    <a class="action-menu-icon" title="<?= htmlReady($label) ?>"
       aria-expanded="false" aria-label="<?= htmlReady($aria_label) ?>" href="#">
        <?= $image ?>
    </a>
    <div class="action-menu-content">
    	<? if (!empty($label)): ?>
        <div class="action-menu-title">
            <?= htmlReady(_($label)) ?>
        </div>
        <? endif; ?>
        <ul class="action-menu-list">
        <? foreach ($actions as $action): ?>
            <li class="action-menu-item">
            <? if ($action['type'] === 'link'): ?>
                <a href="<?= $action['link'] ?>" <?= $attributes($action['attributes']) ?>>
                
                <? if ($has_link_icons): ?>
                    <? if ($action['icon']): ?>
                        <?= $action['icon'] ?>
                    <? else: ?>
                        <span class="action-menu-no-icon"></span>
                    <? endif; ?>
                <? endif; ?>
                    <?= htmlReady($action['label']) ?>
                </a>
            <? elseif ($action['type'] === 'button'): ?>
                <label>
                <? if ($action['icon']): ?>
                    <?= $action['icon']->asInput(['name' => $action['name']]) ?>
                <? else: ?>
                    <span class="action-menu-no-icon"></span>
                    <button type="submit" name="<?= htmlReady($action['name']) ?>" style="display: none;"></button>
                <? endif; ?>
                    <?= htmlReady($action['label']) ?>
                </label>
            <? elseif ($action['type'] === 'multi-person-search'): ?>
                <?= $action['object']->render() ?>
            <? endif; ?>
            </li>
        <? endforeach; ?>
        </ul>
    </div>
</nav>