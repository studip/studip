<? if (count($modul->deskriptoren) > 1): ?>
<div style="width: 100%; text-align: right;">
    <? foreach ($modul->deskriptoren->getAvailableTranslations() as $language) : ?>
        <? $lang = $GLOBALS['MVV_MODUL_DESKRIPTOR']['SPRACHE']['values'][$language]; ?>
        <a data-dialog="size=auto;title='<?= htmlReady($modul->getDisplayName()) ?>'" href="<?= $controller->url_for('/description/' . $modul->id . '/', ['display_language' => $language]) ?>">
            <img src="<?= Assets::image_path('languages/lang_' . mb_strtolower($language) . '.gif') ?>" alt="<?= $lang['name'] ?>" title="<?= $lang['name'] ?>">
        </a>
    <? endforeach; ?>
</div>
<? endif; ?>
<?= $this->render_partial('shared/modul/_modul') ?>
<? if ($type === 1) : ?>
    <?= $this->render_partial('shared/modul/_modullvs') ?>
    <?= $this->render_partial('shared/modul/_pruefungen') ?>
    <?= $this->render_partial('shared/modul/_regularien') ?>
<? endif;?>
<? if ($type === 2): ?>
    <?= $this->render_partial('shared/modul/_modullv') ?>
<? endif; ?>
<? if ($type === 3) : ?>
    <?= $this->render_partial('shared/modul/_modul_ohne_lv') ?>
<? endif; ?>