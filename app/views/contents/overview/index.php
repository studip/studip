<ul class="content-items">
    <? foreach ($tiles as $key => $navigation): ?>
        <? if ($navigation->isVisible() && $key !== 'overview'): ?>
            <li class="content-item content-item-courseware">
                <a href="<?= URLHelper::getLink($navigation->getURL()) ?>" class="content-item-link">
                    <header>
                        <div class="content-item-img-wrapper">
                            <? if ($navigation->getImage()): ?>
                                <?= $navigation->getImage()->asImg(72, $navigation->getLinkAttributes()) ?>
                            <? endif ?>
                        </div>
                        <?= htmlReady($navigation->getTitle()) ?>
                    </header>
                    <p class="content-item-description">
                        <?= htmlReady($navigation->getDescription()) ?>
                    </p>
                </a>
            </li>
        <? endif ?>
    <? endforeach ?>
</ul>
