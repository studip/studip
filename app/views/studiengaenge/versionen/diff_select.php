<h1><?= htmlReady($version->getDisplayName()) ?></h1>
<div>
    <h4><?= _('Vergleich mit folgender Version:') ?></h4>
    <form data-dialog="size=auto" class="mvv-new-tab" action="<?= $controller->link_for('/diff') ?>" method="post">
        <?= CSRFProtection::tokenTag() ?>
        <div>
            <?= $search_version->render(); ?>
            <? if (Request::submitted('search_vesrsion')) : ?>
                <?= Icon::create('refresh', Icon::ROLE_CLICKABLE , ['name' => 'reset_version', 'data-qs_id' => $qs_id_version])->asInput(); ?>
            <? else : ?>
                <?= Icon::create('search', Icon::ROLE_CLICKABLE , ['name' => 'search_version', 'data-qs_id' => $qs_id_version, 'data-qs_name' => $search_version->getId(), 'class' => 'mvv-qs-button', 'data-qs_submit' => ''])->asInput(); ?>
            <? endif; ?>
            <input type="hidden" name="new_id" value="<?= $version->id ?>">
            <input name="version_diff" class="mvv-submit" type="image" title="<?= _('Version suchen') ?>"
                   src="<?= Icon::create('accept')->asImagePath(); ?>">
        </div>
    </form>
</div>
