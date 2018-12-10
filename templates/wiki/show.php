<article class="studip" id="main_content" role="main">
    <header>
        <h1><?= htmlReady($wikipage->keyword) ?></h1>
        <nav>
            <span><?= getZusatz($wikipage) ?></span>
            <? if ($wikipage->isLatestVersion()): ?>
                <? $actionMenu = ActionMenu::get() ?>
                <? if ($wikipage->isEditableBy($GLOBALS['user'])): ?>
                    <? $actionMenu->addLink(
                           URLHelper::getURL('', ['keyword' => $wikipage->keyword, 'view' => 'edit']),
                           _('Bearbeiten'),
                           Icon::create('edit')) ?>
                <? endif ?>
                <? if ($GLOBALS['perm']->have_studip_perm('tutor', Context::getId()) && !$wikipage->isNew()): ?>
                    <? $actionMenu->addLink(
                           URLHelper::getURL('dispatch.php/wiki/change_pageperms', ['keyword' => $wikipage->keyword]),
                           _('Seiten-Einstellungen'),
                           Icon::create('admin'),
                           ['data-dialog' => 'size=auto']) ?>
                    <? $actionMenu->addLink(
                           URLHelper::getURL('', ['keyword' => $wikipage->keyword, 'cmd' => 'delete', 'version' => 'latest']),
                           _('Löschen'),
                           Icon::create('trash')) ?>
                    <? $actionMenu->addLink(
                           URLHelper::getURL('', ['keyword' => $wikipage->keyword, 'cmd' => 'delete_all']),
                           _('Alle Versionen löschen'),
                           Icon::create('trash')) ?>
                <? endif ?>
                <?= $actionMenu->render() ?>
            <? endif ?>
        </nav>
    </header>
    <section>
        <?= $content ?>
    </section>
</article>
