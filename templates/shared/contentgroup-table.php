<?php
$table_rows = 0;
$table_cols = 0;
$max_rows   = 0;
$max_cols   = 0;
if ($rows > 1) {
    $max_rows = $rows;
} else {
    $max_cols = $columns;
}
?>

<? // class "action-menu" will be set from API ?>
<nav <?= arrayToHtmlAttributes($attributes) ?>>
    <a class="action-menu-icon" title="<?= htmlReady($label) ?>"
       aria-expanded="false" aria-label="<?= htmlReady($aria_label) ?>">
        <?= $image ?>
    </a>
    <div class="action-menu-content">
        <div class="action-menu-title">
            <?= htmlReady($label) ?>
        </div>

        <table class="action-menu-table">
            <tr>
            <? foreach ($actions as $action): ?>
                <td>
                <? if ($action['type'] === 'link'): ?>
                    <a href="<?= $action['link'] ?>" <?= arrayToHtmlAttributes($action['attributes']) ?>>
                    <? if ($action['icon']): ?>
                        <?= $action['icon'] ?>
                    <? else: ?>
                        <span class="action-menu-no-icon"></span>
                    <? endif; ?>
                        <br>
                        <div class="navtitle"><?= htmlReady($action['label']) ?></div>
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
                </td>
            <?php
                $table_cols += 1;
                if ($table_cols >= $max_cols) {
                    $table_rows += 1;
                    $table_cols = 0;
                    echo '</tr><tr>'; // Open next row
                }
            ?>
            <? endforeach; ?>
            </tr>
        </table>
    </div>
</nav>
