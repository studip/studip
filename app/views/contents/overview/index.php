 <div class="teaserbox">
    <header>
        <?= _('Alle Inhalte an einem Ort.')?>
    </header>
    <p>
        <?= _('Sie finden in diesem neuen Bereich Zugriff auf Ihre eigenen Inhalte: Courseware Lernmodule, Dateien und freie Lernmaterialien (OER), die Sie auch mit anderen Standorten austauschen können. Ihre Lehrveranstaltungen finden Sie am gewohnten Platz im ')?>
        <a href="<?= URLHelper::getLink('dispatch.php/my_courses/') ?>"><?= _('Veranstaltungsbereich')?></a>.
    </p>
</div>
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
