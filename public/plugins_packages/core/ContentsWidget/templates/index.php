<div class="contents-widget">
    <p>
        <?= _('Suchen, erstellen und finden Sie freie Lernmaterialien!') ?>
    </p>
    <ul class="content-items">
        <li class="content-item content-item-courseware">
            <a href="<?= URLHelper::getLink('dispatch.php/contents/courseware') ?>" class="content-item-link">
                <header><?= _('Courseware') ?></header>
                <p class="content-item-description"><?= _('Schöner lernen mit Stud.IP') ?></p>
                <div class="content-item-img-wrapper">
                    <?= Assets::img('courseware-keyvisual.svg'); ?>
                </div>
            </a>
        </li>
        <li class="content-item content-item-files">
            <a href="<?= URLHelper::getLink('dispatch.php/files/overview') ?>" class="content-item-link">
                <header><?= _('Dateien') ?></header>
                <p class="content-item-description"><?= _('Alle Dokumente an einem Ort') ?></p>
                <div class="content-item-img-wrapper">
                    <?= Assets::img('files-keyvisual.svg'); ?>
                </div>
            </a>
        </li>
        <? if ($show_oer_item): ?>
            <li class="content-item content-item-oer">
                <a href="<?= URLHelper::getLink('dispatch.php/oer/market') ?>" class="content-item-link">
                    <header><?= Config::get()->OER_TITLE ?></header>
                    <p class="content-item-description"><?= _('Freies Wissen für freie Köpfe') ?></p>
                    <div class="content-item-img-wrapper">
                        <?= Assets::img('oer-keyvisual.svg'); ?>
                    </div>
                </a>
            </li>
        <? endif; ?>

    </ul>
</div>