<? $languages = Config::get()->INSTALLED_LANGUAGES ?>
<? if (count($languages) > 1): ?>
<div style="width: 100%; text-align: right;">
    <? foreach ($languages as $language) : ?>
        <a data-dialog="size=auto;title='<?= htmlReady($stg->getDisplayName()) ?>'" href="<?= $controller->url_for('/description/' . $stg->id . '/', array('display_language' => $language)) ?>">
            <img src="<?= Assets::image_path('languages/' . $language['picture']) ?>" alt="<?= $language['name'] ?>" title="<?= $language['name'] ?>">
        </a>
    <? endforeach; ?>
</div>
<? endif; ?>

<?= $this->render_partial('shared/studiengang/_studiengang_info') ?>