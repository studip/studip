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
            URLHelper::getURL('dispatch.php/wiki/change_page_config', ['keyword' => $wikipage->keyword]),
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
<? endif ?>

<div class="contentbar">
    <div class="contentbar_title">
        <a href="<?= URLHelper::getLink('wiki.php', ['keyword' => 'WikiWikiWeb'])?>"
           title="<?= _('Wiki-Startseite') ?>">
            <?=Icon::create('wiki', 'clickable')->asImg(24, ['class' => 'text-bottom']) ?>
        </a>
        <ul class="breadcrumb"><?= $toc_breadcrumb_pages ?></ul>
    </div>

    <div class="contentbar_info">
        <div class="textblock"><?= $toc_breadcrumb_info ?></div>

        <!-- do not show if 0 entries -->
        <? if ($count > 0) : ?>
        <input type="checkbox" id="cb-toc"/>
        <label for="cb-toc" class="check-box" title="<?= _('Inhaltsverzeichnis') ?>" >
            <?= Icon::create('table-of-contents', 'clickable')->asImg(24) ?>
        </label>
        <? endif ?>
        <!-- -->


        <?= $toc_entries ?>

        <a class="consuming_mode_trigger"
           href="#"
           title="<?= _("Konsummodus ein-/ausschalten") ?>">
        </a>

        <? if ($toc_breadcrumb_info) : ?>
            <?= $actionMenu->render() ?>
        <? endif ?>
    </div>

</div>


<script>
    function toggleFullscreen() {

    }

</script>
