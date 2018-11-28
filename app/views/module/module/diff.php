<? require_once 'vendor/php-htmldiff/HtmlDiff.php'; ?>

<style>
    del.diffmod {
        color: red;
    }
    del.diffdel {
        color: red;
    }
    ins.diffmod {
        color: green;
    }
    ins.diffins {
        color: green;
    }
    .mvv-diff {
        margin: 0;
        padding: 0;
        width: 95%;
    }
    .mvv-modul-details
    {
        margin: 10px;
        border-collapse: collapse;
        font-size: 5pt;
        width: 100%;
    }
    .mvv-modul-details td
    {
        padding: 3px;
        vertical-align: top;
        border: 1px solid black;
    }
    .mvv-modul-details th
    {
        border: 1px solid black;
    }
    .mvv-diff-deleted .mvv-modul-details {
        color: red;
    }
    .mvv-diff-deleted .mvv-modul-details td,
    .mvv-diff-deleted .mvv-modul-details th {
        border: solid 1px red;
    }
    .mvv-diff-added .mvv-modul-details {
        color: green;
    }
    .mvv-diff-added .mvv-modul-details td,
    .mvv-diff-added .mvv-modul-details th {
        border: solid 1px green;
    }

</style>
<h2>
    <? printf(_('Vergleich von %s mit %s'), '<span style="font-style: italic">'
            . htmlReady($old_module->getDisplayName()) . '</span>',
            '<span style="font-style: italic;">' . htmlReady($new_module->getDisplayName()) . '</span>'); ?>
</h2>
<div class="mvv-diff">
<?
$old = $this->render_partial('shared/modul/_modul', array('modul' => $old_module));
$new = $this->render_partial('shared/modul/_modul', array('modul' => $new_module));
$diff = new HtmlDiff($old, $new);
$diff->build();
echo $diff->getDifference();
?>
</div>
<? if ($type_old == 1) : ?>
    <? if ($type_new == 1) : ?>
    <div class="mvv-diff">
        <?
        $old = $this->render_partial('shared/modul/_modullvs', array('modul' => $old_module));
        $new = $this->render_partial('shared/modul/_modullvs', array('modul' => $new_module));
        $diff = new HtmlDiff($old, $new);
        $diff->build();
        echo $diff->getDifference();

        $old = $this->render_partial('shared/modul/_pruefungen', array('modul' => $old_module));
        $new = $this->render_partial('shared/modul/_pruefungen', array('modul' => $new_module));
        $diff = new HtmlDiff($old, $new);
        $diff->build();
        echo $diff->getDifference();

        $old = $this->render_partial('shared/modul/_regularien', array('modul' => $old_module));
        $new = $this->render_partial('shared/modul/_regularien', array('modul' => $new_module));
        $diff = new HtmlDiff($old, $new);
        $diff->build();
        echo $diff->getDifference();
        ?>
    </div>
    <? else : ?>
    <div class="mvv-diff mvv-diff-deleted">
        <?= $this->render_partial('shared/modul/_modullvs', array('modul' => $old_module)) ?>
        <?= $this->render_partial('shared/modul/_pruefungen', array('modul' => $old_module)) ?>
        <?= $this->render_partial('shared/modul/_regularien', array('modul' => $old_module)) ?>
    </div>
    <? endif; ?>

    <div class="mvv-diff mvv-diff-added">
    <? if ($type_new == 2) : ?>
        <?= $this->render_partial('shared/modul/_modullv', array('modul' => $new_module)) ?>
    <? endif; ?>
    <? if ($type_new == 3) : ?>
        <?= $this->render_partial('shared/modul/_modul_ohne_lv', array('modul' => $new_module)) ?>
    <? endif; ?>
    </div>

<? endif; ?>

<? if ($type_old == 2) : ?>
    <? if ($type_new == 2) : ?>
    <div class="mvv-diff">
        <?
        $old = $this->render_partial('shared/modul/_modullv', array('modul' => $old_module));
        $new = $this->render_partial('shared/modul/_modullv', array('modul' => $new_module));
        $diff = new HtmlDiff($old, $new);
        $diff->build();
        echo $diff->getDifference();
        ?>
    </div>
    <? else : ?>
    <div class="mvv-diff mvv-diff-deleted">
        <?= $this->render_partial('shared/modul/_modullv', array('modul' => $old_module)) ?>
    </div>
    <? endif; ?>

    <div class="mvv-diff mvv-diff-added">
    <? if ($type_new == 1) : ?>
        <?= $this->render_partial('shared/modul/_modullvs', array('modul' => $old_module)) ?>
        <?= $this->render_partial('shared/modul/_pruefungen', array('modul' => $old_module)) ?>
        <?= $this->render_partial('shared/modul/_regularien', array('modul' => $old_module)) ?>
    <? endif; ?>
    <? if ($type_new == 3) : ?>
        <?= $this->render_partial('shared/modul/_modul_ohne_lv', array('modul' => $new_module)) ?>
    <? endif; ?>
    </div>

<? endif; ?>

<? if ($type_old == 3) : ?>
    <? if ($type_new == 3) : ?>
    <div class="mvv-diff">
        <?
        $old = $this->render_partial('shared/modul/_modul_ohne_lv', array('modul' => $old_module));
        $new = $this->render_partial('shared/modul/_modul_ohne_lv', array('modul' => $new_module));
        $diff = new HtmlDiff($old, $new);
        $diff->build();
        echo $diff->getDifference();
        ?>
    </div>
    <? else : ?>
    <div class="mvv-diff mvv-diff-deleted">
        <?= $this->render_partial('shared/modul/_modul_ohne_lv', array('modul' => $old_module)) ?>
    </div>
    <? endif; ?>

    <div class="mvv-diff mvv-diff-added">
    <? if ($type_new == 1) : ?>
        <?= $this->render_partial('shared/modul/_modullvs', array('modul' => $old_module)) ?>
        <?= $this->render_partial('shared/modul/_pruefungen', array('modul' => $old_module)) ?>
        <?= $this->render_partial('shared/modul/_regularien', array('modul' => $old_module)) ?>
    <? endif; ?>
    <? if ($type_new == 2) : ?>
        <?= $this->render_partial('shared/modul/_modullv', array('modul' => $new_module)) ?>
    <? endif; ?>
    </div>

<? endif; ?>
