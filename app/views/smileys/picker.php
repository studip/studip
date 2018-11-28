<div class="smiley-picker">
    <table class="navigation top">
        <tr>
        <? if ($favorites_activated && count($favorites->get()) > 0): ?>
            <td>
                <a href="<?= $controller->url_for('smileys/picker/favorites') ?>">
                    <?= Icon::create('star', $view === 'favorites' ? 'attention' : 'clickable', ['title' => _('Favoriten')]) ?>
                </a>
            </td>
        <? endif; ?>
            <td style="text-align: right;">
                <a href="<?= $controller->url_for('smileys/picker/all') ?>">
                    <?= Icon::create('smiley', $view === 'all' ? 'attention' : 'clickable', ['title' => _('alle')]) ?>
                </a>
            </td>
        <? for ($i = 0; $i < 26; $i++):
               $char = chr(ord('a') + $i);
        ?>
            <td <? if ($view === $char) echo 'class="active"'; ?>>
            <? if (isset($characters[$char])): ?>
                <a href="<?= $controller->url_for('smileys/picker/'. $char) ?>">
                    <?= mb_strtoupper($char) ?>
                </a>
            <? else: ?>
                <?= $char ?>
            <? endif; ?>
            </td>
        <? endfor; ?>
        </tr>
    </table>

    <div class="smileys">
<? foreach (array_pad($smileys, $controller::GRID_WIDTH * $controller::GRID_HEIGHT, null) as $smiley): ?>
    <? if ($smiley === null): ?>
        <span class="empty"></span>
    <? else: ?>
        <a class="smiley" href="#" data-code="<?= $smiley->short ?: (':' . $smiley->name . ':') ?>">
            <?= $smiley->html ?>
        </a>
    <? endif; ?>
<? endforeach; ?>
    </div>

    <table class="navigation bottom">
        <tr>
            <td>
            <? if ($page > 0): ?>
                <a href="<?= $controller->url_for('smileys/picker/' . $view . '/0') ?>">
                    <?= Icon::create('arr_eol-left', 'clickable')->asImg() ?>
                </a>
                <a href="<?= $controller->url_for('smileys/picker/' . $view . '/' . ($page - 1)) ?>">
                    <?= Icon::create('arr_1left', 'clickable')->asImg() ?>
                </a>
            <? else: ?>
                <?= Icon::create('arr_eol-left', 'inactive')->asImg() ?>
                <?= Icon::create('arr_1left', 'inactive')->asImg() ?>
            <? endif; ?>
            </td>
            <td style="text-align: center;">
                <?= sprintf('Seite %u von %u', $page + 1, $pages + 1) ?>
            </td>
            <td style="text-align: right;">
            <? if ($page < $pages): ?>
                <a href="<?= $controller->url_for('smileys/picker/' . $view . '/' . ($page + 1)) ?>">
                    <?= Icon::create('arr_1right', 'clickable')->asImg() ?>
                </a>
                <a href="<?= $controller->url_for('smileys/picker/' . $view . '/' . $pages) ?>">
                    <?= Icon::create('arr_eol-right', 'clickable')->asImg() ?>
                </a>
            <? else: ?>
                <?= Icon::create('arr_1right', 'inactive')->asImg() ?>
                <?= Icon::create('arr_eol-right', 'inactive')->asImg() ?>
            <? endif; ?>
            </td>
        </tr>
    </table>
</div>