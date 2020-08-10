<article class="studip" id="main_content" role="main">
    <header>
        <h1><?= htmlReady($wikipage->keyword) ?></h1>
        <nav>
            <span><?= getZusatz($wikipage) ?></span>
            <? if ($wikipage->isLatestVersion()): ?>
                <? $actionMenu = ActionMenu::get() ?>
                <? if ($wikipage->isEditableBy($GLOBALS['user'])): ?>
                    <? if (!$wikipage->isNew()): ?>
                        <? $actionMenu->addLink(
                               URLHelper::getURL('dispatch.php/wiki/info', ['keyword' => $wikipage->keyword]),
                               _('Informationen'),
                               Icon::create('info-circle'),
                               ['data-dialog' => 1]
                        ) ?>
                    <? endif ?>
                    <? $actionMenu->addLink(
                           URLHelper::getURL('', ['keyword' => $wikipage->keyword, 'view' => 'edit']),
                           _('Bearbeiten'),
                           Icon::create('edit')
                    ) ?>
                <? endif ?>
                <? if ($GLOBALS['perm']->have_studip_perm('tutor', Context::getId()) && !$wikipage->isNew()): ?>
                    <? $actionMenu->addLink(
                           URLHelper::getURL('dispatch.php/wiki/change_pageperms', ['keyword' => $wikipage->keyword]),
                           _('Seiten-Einstellungen'),
                           Icon::create('admin'),
                           ['data-dialog' => 'size=auto']
                    ) ?>
                    <? $actionMenu->addLink(
                           URLHelper::getURL('', ['keyword' => $wikipage->keyword, 'cmd' => 'really_delete', 'version' => $wikipage->version]),
                           _('Löschen'),
                           Icon::create('trash'),
                           ['data-confirm' => showDeleteDialog($wikipage->keyword, $wikipage->version)]
                    ) ?>
                <? endif ?>
                <?= $actionMenu->render() ?>
            <? endif ?>
        </nav>
    </header>
    <section>
        <? if ($wikipage->keyword == 'Wiki-Startseite' && $wikipage->isNew()): ?>
            <div class="wiki-background">
                <div class="flex">
                    <img src='assets/images/icons/blue/wiki.svg' class="image1">
                    <img src='assets/images/icons/lightblue/wiki.svg' class="image2">
                </div>
            </div>
            <div class="flex">
                <div class="wiki-teaser">
                    <?= _('Mach die Welt ein Stückchen schlauer.') ?>
                </div>
            </div>
        <? else : ?>
            <?= $content ?>
        <? endif ?>
    </section>
    <? if ($wikipage->isEditableBy($GLOBALS['user'])): ?>
        <footer>
            <?= Studip\LinkButton::create(
                $wikipage->isNew() ? _('Neue Seite anlegen') : ('Bearbeiten'),
                URLHelper::getURL('', ['keyword' => $wikipage->keyword, 'view' => 'edit'])
            ) ?>
        </footer>
    <? endif ?>
</article>
