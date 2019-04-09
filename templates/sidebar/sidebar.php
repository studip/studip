<div id="layout-sidebar">
    <section class="sidebar">
<? if ($image): ?>
        <div class="sidebar-image <? if ($avatar) echo 'sidebar-image-with-context'; ?>">
            <?= Assets::img($image, ['alt' => '']) ?>
        <? if ($avatar) : ?>
            <div class="sidebar-context">
                <? if ($avatar->is_customized()) : ?>
                <a href="<?= htmlReady($avatar->getURL(file_exists($avatar->getFilename(Avatar::ORIGINAL)) ? Avatar::ORIGINAL : Avatar::NORMAL)) ?>"
                   data-lightbox="sidebar-avatar"
                   data-title="<?= htmlReady(PageLayout::getTitle()) ?>">
                <? endif ?>
                    <?= $avatar->getImageTag(Avatar::MEDIUM) ?>
                <? if ($avatar->is_customized()) : ?>
                </a>
                <? endif ?>
            </div>
        <? endif ?>
        </div>
<? endif; ?>

    <? foreach ($widgets as $index => $widget): ?>
        <?= $widget->render(['base_class' => 'sidebar']) ?>
    <? endforeach; ?>
    </section>
</div>
