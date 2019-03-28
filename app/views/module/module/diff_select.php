<h1><?= htmlReady($modul->getDisplayName()) ?></h1>
<div>
    <h4><?= _('Vergleich mit folgendem Modul:') ?></h4>
    <form data-dialog="size=auto" class="mvv-new-tab" action="<?= $controller->link_for('/diff') ?>" method="post">
        <?= CSRFProtection::tokenTag() ?>
        <div>
            <?= $search_modul->render(); ?>
            <? if (Request::submitted('search_modul')) : ?>
                <?= Icon::create('refresh', Icon::ROLE_CLICKABLE, ['name' => 'reset_modul', 'data-qs_id' => $qs_id_module])->asInput(); ?>
            <? else : ?>
                <?= Icon::create('search', Icon::ROLE_CLICKABLE, ['name' => 'search_modul', 'data-qs_id' => $qs_id_module, 'data-qs_name' => $search_modul->getId(), 'class' => 'mvv-qs-button', 'data-qs_submit' => ''])->asInput(); ?>
            <? endif; ?>
            <input type="hidden" name="new_id" value="<?= $modul->id ?>">
            <input name="modul_diff" class="mvv-submit" type="image" title="<?= _('Modul suchen') ?>" src="<?= Icon::create('accept')->asImagePath(); ?>">
        </div>
    </form>
</div>
<? if ($quelle) : ?>
<div>
    <h4><?= _('Dieses Modul ist eine Novellierung des Moduls:') ?></h4>
    <a class="mvv-new-tab" href="<?= $controller->link_for('/diff', $modul->id, $quelle->id) ?>"><?= htmlReady($quelle->getDisplayName()) ?></a>
</div>
<? endif; ?>
<? if ($variante) : ?>
<div>
    <h4><?= _('Dieses Modul ist eine Variante von:') ?></h4>
    <a class="mvv-new-tab" href="<?= $controller->link_for('/diff', $modul->id, $variante->id) ?>"><?= htmlReady($variante->getDisplayName()) ?></a>
<? endif; ?>
</div>
<? $variants = $modul->getVariants(); ?>
<? if (count($variants)) : ?>
<div>
    <h4><?= _('Folgende Module sind Varianten dieses Moduls:') ?></h4>
    <ul>
    <? foreach ($variants as $variant) : ?>
        <li>
            <a href="<?= $controller->link_for('/diff', $modul->id, $variant->id) ?>">
            <?= htmlReady($variant->getDisplayName()) ?>
            </a>
        </li>
    <? endforeach; ?>
    </ul>
</div>
<? endif; ?>
