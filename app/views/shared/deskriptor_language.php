<? $table = get_class($modul) == 'Modul' ? 'mvv_modul_deskriptor' : 'mvv_modulteil_deskriptor'; ?>
<? $languages = $modul->deskriptoren->getAvailableTranslations(); ?>
<? foreach ($GLOBALS[strtoupper($table)]['SPRACHE']['values'] as $lang => $value) : ?>
<div style="padding-top:10px;">
    <a href="<?= URLHelper::getLink($link, ['display_language' => $lang]) ?>">
        <img src="<?= Assets::image_path('languages/lang_' . mb_strtolower($lang) . '.gif') ?>" alt="<?= $value['name'] ?>">
        <?= $value['name'] ?> (<?= in_array($lang, $languages) ? 'bearbeiten' : 'neu anlegen' ?>)
        <?= $lang == $sprache ? Icon::create('accept', 'accept', [])->asImg() : '' ?>
    </a>
</div>
<? endforeach; ?>
