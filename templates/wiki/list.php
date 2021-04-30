<div id="main_content" role="main">
<table class="default">
    <colgroup>
        <col>
        <col style="width: 10%;">
        <col style="width: 15%;">
        <col style="width: 35%;">
    </colgroup>
    <thead>
        <tr>
            <th>
                <a href="<?= URLHelper::getLink($url, ['sortby' => $titlesortlink]) ?>">
                    <?= _('Titel') ?>
                </a>
            </th>
            <th>
                <a href="<?= URLHelper::getLink($url, ['sortby' => $versionsortlink]) ?>">
                    <?= _('Änderungen') ?>
                </a>
            </th>
            <th>
                <a href="<?= URLHelper::getLink($url, ['sortby' => $changesortlink]) ?>">
                    <?= _('Letzte Änderung') ?>
                </a>
            </th>
            <th>
                <?= _('Von') ?>
            </th>
        </tr>
    </thead>
    <tbody>
    <? foreach ($pages as $page): ?>
        <tr>
            <td>
                <a href="<?=URLHelper::getLink('wiki.php', ['keyword' => $page->keyword])?>">
                    <?= htmlReady($page->keyword === 'WikiWikiWeb' ? _('Wiki-Startseite') : $page->keyword) ?>
                </a>
            </td>
            <td>
                <?= htmlReady($page->version) ?>
            </td>
            <td>
                <?= date('d.m.Y H:i', $page->chdate) ?>
            <? if ($mode === 'new' && $page->isVisibleTo($GLOBALS['user']) && $page->version > 1): ?>
                <br>
                <a href="<?= URLHelper::getLink('', ['view' => 'diff', 'keyword' => $page->keyword, 'versionssince' => $lastlogindate]) ?>">
                    <?= _('Änderungen') ?>
                </a>
            <? endif; ?>
            </td>
            <td>
                <? if (isset($page->author)) : ?>
                    <a href="<?= URLHelper::getLink('dispatch.php/profile', ['username' => $page->author->username]) ?>">
                        <?= Avatar::getAvatar($page->author->id, $page->author->username)->getImageTag(Avatar::SMALL, ['title' => $page->author->getFullName()]) ?>
                        <?= htmlReady($page->author->getFullName()) ?>
                    </a>
                <? else : ?>
                    <?= Avatar::getNobody()->getImageTag(Avatar::SMALL) ?>
                    <?= _('unbekannt') ?>
                <? endif ?>
            </td>
        </tr>
    <? endforeach; ?>
    </tbody>
</table>
