<p id="pagination-label-<?= $random_id ?>" class="audible">
    <?= _('Blättern') ?>
</p>

<ul class="pagination" role="navigation" aria-labelledby="pagination-label-<?= $random_id ?>">
<? // Create link to last page if we are not on the first page ?>
<? if ($current > 0): ?>
    <li class="prev">
        <button class="pagination--link" name="<?= htmlReady($name) ?>" value="<?= $current - 1 ?>" <? if ($dialog !== null) echo "data-dialog=\"{$dialog}\""; ?>>
            <span class="audible"><?= _('Eine Seite') ?></span>
            <?= _('zurück') ?>
        </button>
    </li>
<? endif; ?>

<? // Create individual pages (with divider for gaps) ?>
<? $last_page = -1; ?>

<? foreach ($pages as $page): ?>
<? if ($page != $last_page + 1): ?>
    <li class="divider" data-skipped="<?= $last_page + 1 ?>-<?= $page - 1 ?>">&hellip;</li>
<? endif; ?>

    <li <? if ($page == $current) echo 'class="current"'; ?>>
        <button class="pagination--link" name="<?= htmlReady($name) ?>" value="<?= $page ?>" <? if ($dialog !== null) echo "data-dialog=\"{$dialog}\""; ?>>
            <span class="audible"><?= _('Seite') ?></span>
            <?= $page + 1 ?>
        </button>
    </li>
<?
    $last_page = $page;
    endforeach;
?>

<? // Create link to next page if we are not on the last page ?>
<? if ($current < $count - 1): ?>
    <li class="next">
        <button class="pagination--link" name="<?= htmlReady($name) ?>" value="<?= $current + 1 ?>" <? if ($dialog !== null) echo "data-dialog=\"{$dialog}\""; ?>>
            <span class="audible"><?= _('Eine Seite') ?></span>
            <?= _('weiter') ?>
        </button>
    </li>
<? endif; ?>

    <li class="divider--template">
        <button class="pagination--link" name="<?= htmlReady($name) ?>" value="{{value}}" <? if ($dialog !== null) echo "data-dialog=\"{$dialog}\""; ?>>
            <span class="audible"><?= _('Seite') ?></span>
            {{label}}
        </button>
    </li>
</ul>
