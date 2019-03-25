<p id="pagination-label-<?= $random_id ?>" class="audible">
    <?= _('Blättern') ?>
</p>

<ul class="pagination" role="navigation" aria-labelledby="pagination-label-<?= $random_id ?>">
<? // Create link to last page if we are not on the first page ?>
<? if ($current > 0): ?>
    <li class="prev">
        <a class="pagination--link" href="<?= $link_for($current - 1) ?>" rel="prev" <? if ($dialog !== null) echo "data-dialog=\"{$dialog}\""; ?>>
            <span class="audible"><?= _('Eine Seite') ?></span>
            <?= _('zurück') ?>
        </a>
    </li>
<? endif; ?>

<? // Create individual pages (with divider for gaps) ?>
<? $last_page = -1; ?>

<? foreach ($pages as $page): ?>
<? if ($page != $last_page + 1): ?>
    <li class="divider" data-skipped="<?= $last_page + 1 ?>-<?= $page ?>">
        &hellip;
    </li>
<? endif; ?>

    <li <? if ($page == $current) echo 'class="current"'; ?>>
        <a class="pagination--link" href="<?= $link_for($page) ?>" <? if ($dialog !== null) echo "data-dialog=\"{$dialog}\""; ?>>
            <span class="audible"><?= _('Seite') ?></span>
            <?= $page + 1 ?>
        </a>
    </li>
<?
    $last_page = $page;
    endforeach;
?>

<? // Create link to next page if we are not on the last page ?>
<? if ($current < $count - 1): ?>
    <li class="next">
        <a class="pagination--link" href="<?= $link_for($current + 1) ?>" rel="next" <? if ($dialog !== null) echo "data-dialog=\"{$dialog}\""; ?>>
            <span class="audible"><?= _('Eine Seite') ?></span>
            <?= _('weiter') ?>
        </a>
    </li>
<? endif; ?>

    <li class="divider--template">
        <a class="pagination--link" href="<?= $link_for('{{value}}') ?>" <? if ($dialog !== null) echo "data-dialog=\"{$dialog}\""; ?>>
            <span class="audible"><?= _('Seite') ?></span>
            {{label}}
        </a>
    </li>
</ul>
