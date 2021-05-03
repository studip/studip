<form class="default">
    <? $base = URLHelper::setBaseURL($GLOBALS['ABSOLUTE_URI_STUDIP']) ?>
    <label>
        <?= _('Platzhalter zum Teilen in Stud.IP (Forum, Blubber, Wiki, Ankündigung)') ?>
        <input type="text" readonly value="[oermaterial]<?= htmlReady($material->getId()) ?>">
    </label>

    <? if (Config::get()->OER_PUBLIC_STATUS === "nobody") : ?>
        <label>
            <?= _('Teilen als Link') ?>
            <input type="text" readonly value="<?= $controller->link_for("oer/market/details/".$material->getId()) ?>">
        </label>
    <? endif ?>

    <? if ($material['player_url'] || $material->isPDF() || $material->isVideo() || $material->isAudio()) : ?>
        <?
        if ($material['player_url']) {
            $url = $material['player_url'];
        } else {
            $url = $material['host_id'] ? $material->host->url."download/".$material['foreign_material_id'] : $controller->link_for("oer/market/download/".$material->getId());
        }
        ?>
        <label>
            <?= _('Teilen als HTML-Schnipsel') ?>
            <textarea readonly><?= htmlReady('<iframe src="'.htmlReady($url).'"></iframe>') ?></textarea>
        </label>

    <? endif ?>
    <? URLHelper::setBaseURL($base) ?>
</form>
