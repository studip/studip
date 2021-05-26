<h1>
    <?= $keyword === 'WikiWikiWeb' ? _("Wiki-Startseite") : htmlReady($keyword) ?>
</h1>

<aside class="wiki-info-aside">
    <table class="default nohover">
        <caption>
            <?= _('Details') ?>
        </caption>

        <tbody>
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
                <td><?= htmlReady($first_user->username) ?></td>
            </tr>
            <tr>
                <td><?= _('Zuletzt geändert') ?></td>
                <td><?= date('d.m.Y, H:i', $last_page['chdate']) ?></td>
            </tr>
            <tr>
                <td><?= _('Geändert von') ?></td>
                <td><?= htmlReady($last_user->username) ?></td>
            </tr>
        </tbody>
    </table>
</aside>

<table class="default nohover wiki-backlinks">
    <caption>
        <?=_('Verweise auf diese Seite') ?>
    </caption>

    <tbody>
        <? if ($backlinks): ?>
            <? foreach (getBacklinks($keyword) as $backlink) : ?>
                <tr>
                    <td>
                        <a href="<?= URLHelper::getLink('wiki.php', ['keyword' => $backlink]) ?>">
                            <?= Icon::create('link-extern') ?>
                            <?= $backlink === 'WikiWikiWeb' ? _('Wiki-Startseite') : htmlReady($backlink) ?>
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

<table class="default nohover wiki-backlinks">
    <caption>
        <?=_('Untergeordnete Seiten') ?>
    </caption>

    <tbody>
        <? if ($descendants): ?>
            <? foreach ($descendants as $descendant) : ?>
                <tr>
                    <td>
                    <a href="<?= URLHelper::getLink('wiki.php', ['keyword' => $descendant->keyword]) ?>">
                            <?= Icon::create('wiki') ?>
                            <?= htmlReady($descendant->keyword) ?>
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
