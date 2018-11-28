<a href="<?= URLHelper::getLink('dispatch.php/multipersonsearch/no_js_form/?name=' . $name); ?>" class="multi_person_search_link" data-dialog="width=720;height=460;id=mp-search"  data-dialogname="<?= $name; ?>" title="<?= htmlReady($title) ?>" data-js-form="<?= URLHelper::getLink('dispatch.php/multipersonsearch/js_form/' . $name); ?>">
    <?
    if (!empty($linkIconPath)) {
        print $linkIconPath->asImg();
    }
    if (!empty($linkIconPath) && !empty($linkText)) {
        print " ";
    }
    if (!empty($linkText)) {
        print $linkText;
    }
    ?>
</a>
