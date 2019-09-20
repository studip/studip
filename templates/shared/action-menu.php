<? // class "action-menu" will be set from API ?>
<nav <?= arrayToHtmlAttributes($attributes) ?>>
    <a class="action-menu-icon" title="<?= _('Aktionen') ?>" aria-expanded="false" aria-label="<?= _("Aktionsmenü") ?>" href="#">
        <div></div>
        <div></div>
        <div></div>
    </a>
    <div class="action-menu-content">
        <div class="action-menu-title">
            <?= _('Aktionen') ?>
        </div>
        <ul class="action-menu-list">
        <? foreach ($actions as $action): ?>
            <li class="action-menu-item <? if (isset($action['attributes']['disabled'])) echo 'action-menu-item-disabled'; ?>">
            <? if ($action['type'] === 'link'): ?>
                <a href="<?= $action['link'] ?>" <?= arrayToHtmlAttributes($action['attributes']) ?>>
                    <? if ($action['icon']): ?>
                        <?= $action['icon'] ?>
                    <? else: ?>
                        <span class="action-menu-no-icon"></span>
                    <? endif ?>
                    <?= htmlReady($action['label']) ?>
                </a>
            <? elseif ($action['type'] === 'button'): ?>
                <? if ($action['icon']): ?>
                    <label class="undecorated">
                        <?= $action['icon']->asInput($action['attributes'] + ['name' => $action['name'], 'title' => $action['label']]) ?>
                        <?= htmlReady($action['label']) ?>
                    </label>
                <? else: ?>
                    <span class="action-menu-no-icon"></span>
                    <button name="<?= htmlReady($action['name']) ?>" <?= arrayToHtmlAttributes($action['attributes']) ?>>
                        <?= htmlReady($action['label']) ?>
                    </button>
                <? endif ?>
            <? elseif ($action['type'] === 'multi-person-search'): ?>
                <?= $action['object']->render() ?>
            <? endif ?>
            </li>
        <? endforeach ?>
        </ul>
    </div>
</nav>
