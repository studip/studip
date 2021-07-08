<div class="contents-widget">
    <p>
        <?= _('Suchen, erstellen und finden Sie freie Lernmaterialien!') ?>
    </p>
    <ul class="content-items">
        <? foreach ($tiles as $key => $navigation): ?>
            <? if ($navigation->isVisible() && $key !== 'overview'): ?>
                <li class="content-item content-item-courseware">
                    <a href="<?= URLHelper::getLink($navigation->getURL()) ?>" class="content-item-link">
                        <div class="content-item-title">
                            <?= htmlReady($navigation->getTitle()) ?>
                        </div>
                        <div class="content-item-img-wrapper">
                            <? if ($navigation->getImage()): ?>
                                <?= $navigation->getImage()->asImg(32, $navigation->getLinkAttributes()) ?>
                            <? endif ?>
                        </div>
                        <p class="content-item-description">
                            <?= htmlReady($navigation->getDescription()) ?>
                        </p>
                    </a>
                </li>
            <? endif ?>
        <? endforeach ?>
    </ul>
</div>
