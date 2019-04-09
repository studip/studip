<?php
    $num_pages = ceil($num_postings / $perPage);
    if ($num_pages <= 1) {
        return;
    }

    $cur_page = $page ?: 1;

    $items = array_unique([1, $cur_page - 2, $cur_page - 1, $cur_page, $cur_page + 1, $cur_page + 2, $num_pages]);
    $items = array_filter($items, function ($item) use ($num_pages) { return $item >= 1 && $item <= $num_pages; });
    sort($items);

    $last_page = reset($items) - 1;
    $random_id = mb_substr(md5(uniqid('pagination', true)), -8);

    $pageparams = $pageparams ?: [];
?>
<p id="pagination-label-<?= $random_id ?>" class="audible">
    <?= _('Blättern') ?>
</p>
<ul class="pagination" role="navigation"
    aria-labelledby="pagination-label-<?= $random_id ?>">
<? if ($cur_page > 1): ?>
    <li class="prev">
        <a class="pagination--link" href="<?= URLHelper::getLink(sprintf($pagelink, $cur_page - 1), $pageparams) ?>" rel="prev" <?= $dialog ?: ''?>>
            <span class="audible"><?= _('Eine Seite') ?></span>
            <?= _('zurück') ?>
        </a>
    </li>
<? endif; ?>
<? foreach ($items as $item): ?>
<? if ($item != $last_page + 1): ?>
    <li class="divider" data-skipped="<?= $last_page + 1 ?>-<?= $item - 1 ?>">&hellip;</li>
<? endif; ?>
    <li <? if ($item == $cur_page) echo 'class="current"'; ?>>
        <a class="pagination--link" href="<?= URLHelper::getLink(sprintf($pagelink, $item), $pageparams) ?>" <?= $dialog ?: ''?>>
            <span class="audible"><?= _('Seite') ?></span>
            <?= $item ?>
        </a>
    </li>
<?
    $last_page = $item;
    endforeach;
?>
<? if ($cur_page < $num_pages): ?>
    <li class="next">
        <a class="pagination--link" href="<?= URLHelper::getLink(sprintf($pagelink, $cur_page + 1), $pageparams) ?>" rel="next" <?= $dialog ?: ''?>>
            <span class="audible"><?= _('Eine Seite') ?></span>
            <?= _('weiter') ?>
        </a>
    </li>
<? endif; ?>
</ul>
