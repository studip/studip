<? foreach ($actions as $action): ?>
    <? if ($action['type'] === 'link'): ?>
        <a href="<?= $action['link'] ?>" <?= arrayToHtmlAttributes($action['attributes'] + ['title' => $action['label']]) ?>>
            <? if ($action['icon']): ?>
                <?= $action['icon'] ?>
            <? else: ?>
                <?= htmlReady($action['label']) ?>
            <? endif ?>
        </a>
    <? elseif ($action['type'] === 'button'): ?>
        <? if ($action['icon']): ?>
            <?= $action['icon']->asInput($action['attributes'] + ['name' => $action['name'], 'title' => $action['label']]) ?>
        <? else: ?>
            <button name="<?= htmlReady($action['name']) ?>" <?= arrayToHtmlAttributes($action['attributes']) ?>>
                <?= htmlReady($action['label']) ?>
            </button>
        <? endif ?>
    <? elseif ($action['type'] === 'multi-person-search'): ?>
        <?= $action['object']->render() ?>
    <? endif ?>
<? endforeach ?>
