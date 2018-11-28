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
            . htmlReady($old_version->getDisplayName()) . '</span>',
            '<span style="font-style: italic;">' . htmlReady($new_version->getDisplayName()) . '</span>'); ?>
</h2>
<div class="mvv-diff">
<?
$old = $this->render_partial('shared/version/_version', ['version' => $old_version]);
$new = $this->render_partial('shared/version/_version', ['version' => $new_version]);
$diff = new HtmlDiff($old, $new);
$diff->build();
echo $diff->getDifference();

$old = $this->render_partial('shared/version/_versionmodule', ['version' => $old_version]);
$new = $this->render_partial('shared/version/_versionmodule', ['version' => $new_version]);
$diff = new HtmlDiff($old, $new);
$diff->build();
echo $diff->getDifference();
?>
</div>
