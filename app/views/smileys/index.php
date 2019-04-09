<?php
use Studip\Button;

// divide smiley array in equal chunks, spillover from left to right
$count     = count($smileys);
$columns   = min(3, ceil($count / 5));

$max       = $columns ? floor($count / $columns) : 0;
$spillover = $columns ? $count % $columns : 0;

$data = [];
for ($i = 0; $i < $columns; $i++) {
    $num = $max + (int)($spillover > 0);

    $data[] = array_splice($smileys, 0, $num);

    $spillover -= 1;
}
$data = array_filter($data);
?>

<ul class="smiley-tabs" role="navigation">
<? if ($favorites_activated): ?>
    <li class="favorites <? if ($view === 'favorites') echo 'current'; ?>">
        <a href="<?= $controller->url_for('smileys/index/favorites') ?>" data-dialog>
            <?= _('Favoriten') ?>
        </a>
    </li>
<? endif; ?>
<? if (Smiley::getShort()): ?>
    <li <? if ($view === 'short') echo 'class="current"'; ?>>
        <a href="<?= $controller->url_for('smileys/index/short') ?>" data-dialog>
            <?= _('Kürzel') ?>
        </a>
    </li>
<? endif; ?>
    <li <? if ($view === 'all') echo 'class="current"'; ?>>
        <a href="<?= $controller->url_for('smileys/index/all') ?>" data-dialog>
            <?= _('Alle') ?>
        </a>
    </li>
<? foreach (array_keys($characters) as $char): ?>
    <li <? if ($view === $char) echo 'class="current"'; ?>>
        <a href="<?= $controller->url_for('smileys/index', $char) ?>" data-dialog>
            <?= mb_strtoupper($char) ?>
        </a>
    </li>
<? endforeach; ?>
</ul>

<div class="clear"></div>

<? if (!$count): ?>
    <?= MessageBox::info($view === 'favorites'
                         ? _('Keine Favoriten vorhanden.')
                         : _('Keine Smileys vorhanden.')) ?>
<? else: ?>
    <table class="smiley-container">
        <tr>
        <? foreach ($data as $smileys): ?>
            <td valign="top" align="center">

                <table class="smiley-column default">
                    <colgroup>
                        <col>
                        <col width="25%">
                        <col width="25%">
                    <? if ($favorites_activated): ?>
                        <col width="32px">
                    <? endif; ?>
                    </colgroup>
                    <thead>
                        <tr>
                            <th><?= _('Bild') ?></th>
                            <th><?= _('Code') ?></th>
                            <th><?= _('Kürzel') ?></th>
                        <? if ($favorites_activated): ?>
                            <th class="actions">
                                <abbr title="<?= _('Favorit') ?>">
                                    <?= Icon::create('star', 'info')->asImg() ?>
                                </abbr>
                            </th>
                        <? endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                    <? foreach ($smileys as $smiley): ?>
                        <tr id="smiley<?= $smiley->id ?>">
                            <td class="smiley-icon">
                                <a name="smiley<?= $smiley->id ?>"></a>
                                <?= $smiley->getImageTag() ?>
                            </td>
                            <td><?= sprintf(':%s:', $smiley->name) ?></td>
                            <td><?= htmlReady($smiley->short) ?></td>
                        <? if ($favorites_activated): ?>
                            <td class="actions">
                                <a href="<?= $controller->url_for('smileys/favor', $smiley->id, $view) ?>"
                                   class="smiley-toggle <?= $favorites->contain($smiley->id) ? 'favorite' : '' ?>">
                                <? if ($favorites->contain($smiley->id)): ?>
                                    <?= _('Als Favorit entfernen') ?>
                                <? else: ?>
                                    <?= _('Als Favorit markieren') ?>
                                <? endif; ?>
                                </a>
                            </td>
                        <? endif; ?>
                        </tr>
                    <? endforeach; ?>
                    </tbody>
                </table>

            </td>
        <? endforeach; ?>
        </tr>
    </table>
<? endif; ?>

