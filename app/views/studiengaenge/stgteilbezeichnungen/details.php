<? $languages = Config::get()->CONTENT_LANGUAGES; ?>
<? $def_lang = reset(array_keys($languages)); ?>
<td colspan="4">
    <table class="default nohover">
        <tbody>
            <tr>
                <th><?= _('Name') ?></th>
            </tr>
            <tr>
                <td>
                    <img src="<?= Assets::image_path('languages/' . $languages[$def_lang]['picture']) ?>"
                         alt="<?= $languages[$def_lang]['name'] ?>">
                    <?= htmlReady($stgteilbezeichnung->isI18nField('name')
                        ? $stgteilbezeichnung->name->original()
                        : $stgteilbezeichnung->name) ?>
                </td>
            </tr>
            <? if ($stgteilbezeichnung->isI18nField('name')) : ?>
            <?php $stgteilbezeichnung_arr = $stgteilbezeichnung->name->toArray();?>
                <? foreach ($stgteilbezeichnung_arr as $locale => $localized) : ?>
                    <tr>
                        <td>
                            <img src="<?= Assets::image_path('languages/' . $languages[$locale]['picture']) ?>"
                                 alt="<?= $languages[$locale]['name'] ?>">
                            <?= htmlReady($localized) ?>
                        </td>
                    </tr>
                <? endforeach; ?>
            <? endif; ?>
            <? if ($stgteilbezeichnung->isI18nField('name_kurz')) : ?>
                <? if ($stgteilbezeichnung->name_kurz->original() || count($stgteilbezeichnung->name_kurz->toArray())) : ?>
                    <tr>
                        <th><strong><?= _('Kurzname:') ?></strong></th>
                    </tr>
                    <? if ($stgteilbezeichnung->name_kurz->original()) : ?>
                        <tr>
                            <td>
                                <img src="<?= Assets::image_path('languages/' . $languages[$def_lang]['picture']) ?>"
                                     alt="<?= $languages[$def_lang]['name'] ?>">
                                <?= htmlReady($stgteilbezeichnung->name_kurz->original()) ?>
                            </td>
                        </tr>
                    <? endif; ?>
                    <?php $stgteilbezeichnung_arr = $stgteilbezeichnung->name_kurz->toArray() ?>
                    <? foreach ($stgteilbezeichnung_arr as $locale => $localized) : ?>
                        <tr>
                            <td>
                                <img src="<?= Assets::image_path('languages/' . $languages[$locale]['picture']) ?>"
                                     alt="<?= $languages[$locale]['name'] ?>">
                                <?= htmlReady($localized) ?>
                            </td>
                        </tr>
                    <? endforeach; ?>
                <? endif; ?>
            <? else : ?>
                <tr>
                    <th><strong><?= _('Kurzname:') ?></strong></th>
                </tr>
                <tr>
                    <td>
                        <?= htmlReady($stgteilbezeichnung->name_kurz) ?>
                    </td>
                </tr>
            <? endif; ?>
        </tbody>
    </table>
</td>