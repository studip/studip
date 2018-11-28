<?php
if (!$selected_terms_of_use_id) {
    $selected_terms_of_use_id = ContentTermsOfUse::findDefault()->id;
}
?>
<? if ($content_terms_of_use_entries): ?>
    <fieldset class="select_terms_of_use">
    <? foreach ($content_terms_of_use_entries as $content_terms_of_use_entry) : ?>
        <input type="radio" name="content_terms_of_use_id"
               value="<?= htmlReady($content_terms_of_use_entry->id) ?>"
               id="content_terms_of_use-<?= htmlReady($content_terms_of_use_entry->id) ?>"
               <? if ($content_terms_of_use_entry->id == $selected_terms_of_use_id) echo 'checked'; ?>>

        <label for="content_terms_of_use-<?= htmlReady($content_terms_of_use_entry->id) ?>">
            <div class="icon">
            <? if ($content_terms_of_use_entry['icon']) : ?>
                <? if (filter_var($content_terms_of_use_entry['icon'], FILTER_VALIDATE_URL)): ?>
                    <img src="<?= htmlReady($content_terms_of_use_entry['icon']) ?>" width="32" height="32">
                <? else : ?>
                    <?= Icon::create($content_terms_of_use_entry['icon'], Icon::ROLE_CLICKABLE)->asImg(32) ?>
                <? endif ?>
            <? endif ?>
            </div>
            <div class="text">
                <?= htmlReady($content_terms_of_use_entry->name) ?>
            </div>
            <?= Icon::create('arr_1down', Icon::ROLE_CLICKABLE)->asImg(24, ['class' => 'arrow']) ?>
            <?= Icon::create('check-circle', Icon::ROLE_CLICKABLE)->asImg(32, ['class' => 'check']) ?>
        </label>

        <? if (trim($content_terms_of_use_entry->description)): ?>
            <div class="terms_of_use_description">
                <div class="description">
                    <?= formatReady($content_terms_of_use_entry->description ?: _('Keine Beschreibung')) ?>
                </div>
            </div>
        <? endif; ?>
    <? endforeach; ?>
    </fieldset>
<? endif; ?>
