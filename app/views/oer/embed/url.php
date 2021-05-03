<?php
if ($material['player_url']) {
    OERDownloadcounter::addCounter($material->id);
    $url = $material['player_url'];
}
$htmlid = "oercampus_".$material->id."_".uniqid();
?>
<iframe id='<?= $htmlid ?>'
        src="<?= htmlReady($url) ?>"
        style="width: 100%; height: 70vh; border: none;"></iframe>
<?= $this->render_partial("oer/embed/_link") ?>
