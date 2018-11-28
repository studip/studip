<?
$absTime = strftime('%x %X', $searchResult['fileRef']->chdate);
$relTime = reltime($searchResult['fileRef']->chdate);
?>
<span class="time"  title="<?= htmlReady($absTime) ?>">
    <?= $searchResult['fileRef']->chdate ? htmlReady($relTime) : '' ?>
</span>
