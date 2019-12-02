<aside>
    <table class="default nohover">
        <caption>
            <?= htmlReady($keyword) ?>
        </caption>

        <tbody>
            <? $last_page = WikiPage::findLatestPage($this->range_id, $this->keyword);
               $last_user = User::find($last_page['user_id']);
               $first_page = getWikiPage($this->keyword, 1);
               $first_user = User::find($first_page['user_id']);
            ?>
            <tr>
                <td><?= _('Version') ?></td>
                <td><?= $last_page['version'] ?></td>
            </tr>
            <tr>
                <td><?= _('Erstellt') ?></td>
                <td><?= date('d.m.Y, H:i', $first_page['chdate']) ?></td>
            </tr>
            <tr>
                <td><?= _('Erstellt von') ?></td>
                <td><?= $first_user->username ?></td>
            </tr>
            <tr>
                <td><?= _('Zuletzt geändert') ?></td>
                <td><?= date('d.m.Y, H:i', $last_page['chdate']) ?></td>
            </tr>
            <tr>
                <td><?= _('Geändert von') ?></td>
                <td><?= $last_user->username ?></td>
            </tr>
        </tbody>
    </table>
</aside>

<table class="default nohover">
    <caption>
        <?=_('Verweise auf diese Seite') ?>
    </caption>

    <tbody>
        <? if ($backlinks): ?>
            <? foreach (getBacklinks($keyword) as $backlink) : ?>
                <tr>
                    <td>
                        <a href="<?= URLHelper::getURL('wiki.php', ['keyword' => $backlink]) ?>">
                            <?= Icon::create('link-extern') ?>
                            <?= htmlReady($backlink) ?>
                        </a>
                    </td>
                </tr>
            <? endforeach ?>
        <? else: ?>
            <tr>
                <td><?= _('keine') ?></td>
            </tr>
        <? endif ?>
    </tbody>
</table>
