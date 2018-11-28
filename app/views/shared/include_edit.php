<div id="mvv-include-edit<?= $item_id ?>" class="mvv-include-edit">
    <a class="mvv-include-close"></a>
    <div class="mvv-include-background">
        <div class="mvv-include-content">
            <h2 class="topic"><?= htmlReady(PageLayout::getTitle()) ?></h2>
            <?= $content_for_layout ?>
        </div>
    </div>
    <div class="mvv-edit-bottom"></div>
</div>
