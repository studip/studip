<h1>
    <?= htmlReady($keyword) ?> - <?= _('Versionshistorie') ?>
</h1>

<table class="default">
    <colgroup>
        <col style="width: 10%;">
        <col style="width: 30%;">
        <col style="width: 70%;">
    </colgroup>

    <thead>
        <tr>
            <th>
                <a href="<?= URLHelper::getLink($url, ['keyword' => $keyword, 'sortby' => $versionsortlink]) ?>">
                    <?= _('Versionsnummer') ?>
                </a>
            </th>
            <th>
                <?= _('Autor/in') ?>
            </th>
            <th>
                <a href="<?= URLHelper::getLink($url, ['keyword' => $keyword, 'sortby' => $changesortlink]) ?>">
                    <?= _('Erstellt am') ?>
                </a>
            </th>
            <th>
                <?= _('Löschen') ?>
            </th>
        </tr>
    </thead>

    <tbody>
        <? foreach ($pages as $page): ?>
            <tr>
                <td>
                <a href="<?= URLHelper::getLink('', ['keyword' => $keyword, 'version' => $page->version]) ?>">
                    <?= _('Version') ?> <?= $page->version ?>
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
                <td>
                    <?= date('d.m.Y H:i', $page->chdate) ?>
                </td>
                <td>
                    <a href="<?= URLHelper::getURL('', ['keyword' => $keyword, 'cmd' => 'delete', 'version' => $page->version])  ?>">
                        <?= Icon::create('trash')->asImg(tooltip2('Version löschen')) ?>
                    </a>
                </td>
            </tr>
        <? endforeach ?>
    </tbody>

    <tfoot>
        <tr>
            <td colspan="4">
                <?= Studip\LinkButton::create(
                    _('Alle Versionen löschen'),
                    URLHelper::getURL('', ['keyword' => $keyword, 'sortby' => $sortby, 'cmd' => 'delete_all_versions'])
                ) ?>
            </td>
        </tr>
    </tfoot>
</table>
