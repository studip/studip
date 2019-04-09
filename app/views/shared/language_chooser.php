<div class="mvv-widget-right">
    <div class="mvv-inst-chooser">
        <select name="<?= $chooser_id ?>">
            <option class="mvv-inst-chooser-level" value=""><?= _('-- bitte wählen --'); ?></option>
        <? foreach ($chooser_languages as $key => $language) : ?>
            <option class="" data-fb="<?= $key ?>" value="<?= $key ?>">
                <?= htmlReady($language['name']); ?>
            </option>
        <? endforeach; ?>
        </select>
        <span class="mvv-inst-add-button"><?= Icon::create('arr_2up', 'clickable', ['title' => _('Sprache zuordnen')])->asImg(); ?></span>
    </div>
</div>