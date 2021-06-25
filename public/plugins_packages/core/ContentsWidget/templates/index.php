<div class="contents-widget">
    <p>
        <?= _('Suchen, erstellen und finden Sie freie Lernmaterialien!') ?>
    </p>
    <ul class="content-items">
        <? foreach ($tiles as $key => $navigation): ?>
            <? if ($navigation->isVisible() && $key !== 'overview'): ?>
                <li class="content-item content-item-courseware">
                    <a href="<?= URLHelper::getLink($navigation->getURL()) ?>" class="content-item-link">
                        <header>
                            <?= htmlReady($navigation->getTitle()) ?>
                        </header>
                        <p class="content-item-description">
                            <?= htmlReady($navigation->getDescription()) ?>
                        </p>
                        <? if ($navigation->getImage()): ?>
                            <div class="content-item-img-wrapper">
                                <?= $navigation->getImage()->asImg(false, $navigation->getLinkAttributes()) ?>
                            </div>
                        <? endif ?>
                    </a>
                </li>
            <? endif ?>
        <? endforeach ?>
    </ul>
</div>
