<article class="contentbox">
    <a href="<?= $controller->url_for('oer/market/details/' . $material->getId()) ?>">
        <header>
            <h1>
                <?
                if ($material['category'] === "video") {
                    $icon = "video";
                }
                if ($material['category'] === "audio") {
                    $icon = "file-audio";
                }
                if ($material['category'] === "presentation") {
                    $icon = "file-pdf";
                }
                if ($material['category'] === "elearning") {
                    $icon = "learnmodule";
                }
                if ($material['content_type'] === "application/zip") {
                    $icon = "archive3";
                }
                if (!$icon) {
                    $icon = "file";
                }
                ?>
                <?= Icon::create($icon, Icon::ROLE_CLICKABLE)->asImg(20, ['class' => "text-bottom"]) ?>
                <div class="title"><?= htmlReady($material['name']) ?></div>
            </h1>
        </header>
        <div class="image" style="background-image: url(<?= $material->getLogoURL() ?>);<?= (!$material['front_image_content_type']) ? " background-size: 60% auto;" : "" ?>"></div>
    </a>
</article>
