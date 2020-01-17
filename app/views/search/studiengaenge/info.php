<div style="width: 100%; text-align: right;">
    <? foreach (Config::get()->CONTENT_LANGUAGES as $locale => $language) : ?>
        <a data-dialog="size=auto;title='<?= htmlReady($studiengang->getDisplayName()) ?>'" href="<?= $controller->url_for('/info/' . $studiengang->id . '/', ['language' => $locale]) ?>">
            <img src="<?= Assets::image_path('languages/' . $language['picture']) ?>" alt="<?= $language['name'] ?>" title="<?= $language['name'] ?>">
        </a>
    <? endforeach; ?>
</div>
<article class="studip">
    <header>
        <h1><?= _('Zahlen und Fakten') ?></h1>
    </header>
    <table class="default">
        <colgroup>
            <col width="40%">
        </colgroup>
        <tbody>
            <tr>
                <td>
                    <strong><?= _('Studiengangsname') ?>:</strong>
                </td>
                <td>
                    <?= htmlReady($studiengang->getDisplayName()) ?>
                </td>
            </tr>
            <tr>
                <td>
                    <strong><?= _('Dauer') ?>:</strong>
                </td>
                <td>
                    <? printf('%s Semester', htmlReady($studiengang->studienzeit)) ?>
                </td>
            </tr>
            <tr>
                <td>
                    <strong><?= _('Abschlussgrad') ?>:</strong>
                </td>
                <td>
                    <?= htmlReady($GLOBALS['MVV_STUDIENGANG']['ABSCHLUSSGRAD']['values'][$studiengang->abschlussgrad]['name']) ?>
                </td>
            </tr>
            <tr>
                <td>
                    <strong><?= _('Sprache') ?>:</strong>
                </td>
                <td>
                    <?= htmlReady(implode('/', $studiengang->languages->pluck('display_name'))) ?>
                </td>
            </tr>
            <tr>
                <td>
                    <strong><?= _('Art der Zulassung') ?>:</strong>
                </td>
                <td>
                    <?= htmlReady($GLOBALS['MVV_STUDIENGANG']['ZULASSUNG']['values'][$studiengang->enroll]['name']) ?>
                </td>
            </tr>
            <tr>
                <td>
                    <strong><?= _('Besondere Zugangsvoraussetzungen') ?>:</strong>
                </td>
                <td>

                </td>
            </tr>
            <? foreach ($studiengang->datafields->getTypedDatafield() as $entry) : ?>
                <? if (mb_strpos($entry->datafield->object_class, 'settings') !== false
                        && $entry->isVisible() && $entry->getValue()) : ?>
                    <tr>
                        <td>
                            <strong><?= htmlReady($entry->getName()) ?></strong>
                        </td>
                        <td>
                            <?= $entry->getDisplayValue() ?>
                        </td>
                    </tr>
                <? endif; ?>
            <? endforeach; ?>
        </tbody>
    </table>
</article>
<article class="studip">
    <header>
        <h1><?= _('Beschreibung') ?></h1>
    </header>
    <section>
        <?= formatReady($studiengang->beschreibung) ?>
    </section>
</article>
<? foreach ($studiengang->datafields as $df) : ?>
    <? if (mb_strpos($df->datafield->object_class, 'settings') !== false) : ?>
        <? $tdf = $df->getTypedDatafield(); ?>
        <? if ($tdf->isVisible() && $tdf->getValue()) : ?>
            <article class="studip">
                <header>
                    <h1><?= htmlReady($tdf->getName()) ?></h1>
                </header>
                <section>
                    <?= $tdf->getDisplayValue() ?>
                </section>
            </article>
        <? endif; ?>
    <? endif; ?>
<? endforeach; ?>
<? foreach ($studiengang->datafields as $df) : ?>
    <? if (mb_strpos($df->datafield->object_class, 'info') !== false) : ?>
        <? $tdf = $df->getTypedDatafield(); ?>
        <? if ($tdf->isVisible() && $tdf->getValue()) : ?>
            <article class="studip">
                <header>
                    <h1><?= htmlReady($tdf->getName()) ?></h1>
                </header>
                <section>
                    <?= $tdf->getDisplayValue() ?>
                </section>
            </article>
        <? endif; ?>
    <? endif; ?>
<? endforeach; ?>
<article class="studip">
    <header>
        <h1><?= _('Dokumente: Ordnungen, Formulare, Informationen') ?></h1>
    </header>
    <section>
        <? foreach ($all_documents as $category => $files) : ?>
            <? if ($GLOBALS['MVV_DOCUMENTS']['CATEGORY']['values'][$category]['visible']) : ?>
                <strong><?= htmlReady($GLOBALS['MVV_DOCUMENTS']['CATEGORY']['values'][$category]['name']) ?></strong>
                <ul>
                    <? foreach ($files as $file) : ?>
                    <li>
                        <? if ($file['is_link']) : ?>
                            <a href="<?= $file['url'] ?>" target="_blank"><?= htmlReady($file['name']) ?></a>
                        <? else : ?>
                        <?= htmlReady($file['name']) ?>
                        <a href="<?= $file['url'] ?>"><?= htmlReady($file['extension']) ?></a>
                        <? endif ?>
                    </li>
                    <? endforeach; ?>
                </ul>
            <? endif; ?>
        <? endforeach; ?>
    </section>
</article>
