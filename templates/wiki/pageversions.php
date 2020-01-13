<table class="default">

    <caption>
        <?= htmlReady($keyword) ?> - <?= _('Versionshistorie') ?>
    </caption>
 
    <colgroup>
        <col style="width: 10%;">
        <col style="width: 30%;">
        <col style="width: 70%;">
    </colgroup>

    <thead>
        <tr>
            <th>
                <a href="<?= URLHelper::getLink($url, ['keyword' => $keyword, 'sortby' => $versionsortlink]) ?>">
                    <?= _('Version') ?>
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
                <?= _('Aktion') ?>
            </th>
        </tr>
    </thead>

    <tbody>
        <? foreach ($pages as $page): ?>
            <tr>
                <td>
                <a href="<?= URLHelper::getLink('', ['keyword' => $keyword, 'version' => $page->version]) ?>">
                    <?= $page->version ?>
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
                <td style="text-align: right";>
                    <a href="<?= URLHelper::getURL("?cmd=really_delete&keyword=".urlencode($keyword)."&version={$page->version}") ?>">
                        <?= Icon::create('trash')->asInput(tooltip2(_('löschen')) + ['data-confirm' => showDeleteDialog($keyword, $page->version)]) ?>
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
                    URLHelper::getURL("?cmd=really_delete_all&keyword=".urlencode($keyword)),
                    ['data-confirm' => showDeleteAllDialog($keyword)]
                ) ?>
            </td>
        </tr>
    </tfoot>
</table>