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
                           URLHelper::getURL('', ['keyword' => $wikipage->keyword, 'cmd' => 'delete', 'version' => 'latest']),
                           _('Löschen'),
                           Icon::create('trash')
                    ) ?>
                <? endif ?>
                <?= $actionMenu->render() ?>
            <? endif ?>
        </nav>
    </header>
    <section>
        <? if ($wikipage->keyword == 'WikiWikiWeb' && $wikipage->isNew()): ?>
            <style>
                .wiki_background {
                   background-image:url(assets/images/icons/lightblue/wiki.svg);
                   background-repeat:no-repeat;
                   background-size:260px;
                   background-position:center;
                   background-color: hsla(0,0%,100%,0.70);
                   background-blend-mode: overlay;
                }
                .flex {
                   display:flex;
                   justify-content:center;
                }
            </style>
            <div class="wiki_background">
                <div class="flex">
                    <img src='assets/images/icons/blue/wiki.svg' style="height:140x;width:160px;margin-top:90px;margin-left:10px";>
                    <img src='assets/images/icons/lightblue/wiki.svg' style="height:180px;width:200px;margin-top:120px;margin-left:10px";>
                </div>
            </div>
            <div class="flex" style="color:#28497c;">
                <?= _('Mach die Welt ein Stückchen schlauer.') ?>
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
