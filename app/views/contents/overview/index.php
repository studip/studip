<div class="teaserbox">
    <header>
        <?= _('Alle Inhalte an einem Ort. - Super, oder?')?>
    </header>
    <p>
        <?= _('Sie finden in diesem neuen Bereich Zugriff auf Ihre eigenen Inhalte: Courseware Lernmodule, Dateien und freie Lernmaterialien (OER), die Sie auch mit anderen Standorten austauschen können. Ihre Lehrveranstaltungen finden Sie am gewohnten Platz im ')?>
        <a href="<?= URLHelper::getLink('dispatch.php/my_courses/') ?>"><?= _('Veranstaltungsbereich')?></a>.
    </p>
</div>
<ul class="content-items">
    <li class="content-item content-item-courseware">
        <a href="<?= URLHelper::getLink('dispatch.php/contents/courseware') ?>">
            <header><?= _('Courseware')?></header>
            <p class="content-item-description"><?= _('Schöner lernen mit Stud.IP')?></p>
            <div class="content-item-img-wrapper">
                <?= Assets::img('courseware-keyvisual.svg');?>
            </div>
        </a>
    </li>
    <li class="content-item content-item-oer">
        <a href="<?= URLHelper::getLink('dispatch.php/oer/market') ?>">
            <header><?= _('OER-Campus')?></header>
            <p class="content-item-description"><?= _('Freies Wissen für freie Köpfe')?></p>
            <div class="content-item-img-wrapper">
                <?= Assets::img('oer-keyvisual.svg');?>
            </div>
        </a>
    </li>
    <li class="content-item content-item-files">
        <a href="<?= URLHelper::getLink('dispatch.php/files/overview') ?>">
            <header><?= _('Dateien')?></header>
            <p class="content-item-description"><?= _('Alle Dokumente an einem Ort')?></p>
            <div class="content-item-img-wrapper">
                <?= Assets::img('files-keyvisual.svg');?>
            </div>
        </a>
    </li>
</ul>