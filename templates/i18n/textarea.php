<? foreach ($languages as $locale => $lang): ?>
    <?
        $attr = $attributes;
        if ($locale === $base_lang) {
            $attr['name'] = $name;
            $text = $value->original();
        } else {
            $attr['name'] = "{$name}_i18n[{$locale}]";
            $text = $value->translation($locale);

            if (isset($attr['id'])) {
                unset($attr['id']);
            }

            // Remove required attribute if no text has been set
            if (isset($attr['required']) && !$attr['value']) {
                unset($attr['required']);
            }
        }

        // If special attribute locale_names is defined, use name from that
        if (isset($attr['locale_names']) && is_array($attr['locale_names'])) {
            $attr['name'] = $attr['locale_names'][$locale];
            unset($attr['locale_names']);
        }
    ?>
    <div class="i18n" data-lang="<?= $lang['name'] ?>" data-icon="url(<?= Assets::image_path("languages/{$lang['picture']}") ?>)">
        <textarea <?= arrayToHtmlAttributes($attr) ?>
        ><?= $wysiwyg ? wysiwygReady($text) : htmlReady($text) ?></textarea>
    </div>
<? endforeach; ?>
