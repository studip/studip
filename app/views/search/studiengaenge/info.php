<style>
    /* This should be done by an own class (maybe not table? maybe dd?) */
    #tablefix {
        padding: 0;
    }
    #tablefix > header {
        margin: 0px;
    }
    #tablefix table {
        margin-bottom: 0;
        border-bottom: 0;
    }
    #tablefix table tbody tr:last-child td {
        border-bottom: 0;
    }
    input[type=checkbox].mvv-cb-more-items {
        display: none;
    }
    input[type=checkbox].mvv-cb-more-items:checked~ul li:nth-child(n+5) {
        height: 0;
        visibility: hidden;
    }
    input[type=checkbox].mvv-cb-more-items:not(:checked)~ul li label.cb-more-items {
        display: none;
    }
    input[type=checkbox].mvv-cb-more-items:checked~ul li label.cb-more-items {
        display: block;
    }
</style>
<div style="width: 100%; text-align: right;">
    <? foreach (Config::get()->CONTENT_LANGUAGES as $locale => $language) : ?>
        <a data-dialog="title='<?= htmlReady($studiengang->getDisplayName()) ?>'" href="<?= $controller->url_for('/info/' . $studiengang->id . '/', ['language' => $locale]) ?>">
            <img src="<?= Assets::image_path('languages/' . $language['picture']) ?>" alt="<?= $language['name'] ?>" title="<?= $language['name'] ?>">
        </a>
    <? endforeach; ?>
</div>
<article class="studip toggle open" id="tablefix">
    <header>
        <h1><a name=""><?= _('Zahlen und Fakten') ?></a></h1>
    </header>
    <table class="default">
        <colgroup>
            <col>
            <col style="width: 80%;">
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
            <? if (count($studiengang->languages)) : ?>
                <tr>
                    <td>
                        <strong><?= _('Sprache') ?>:</strong>
                    </td>
                    <td>
                        <?= htmlReady(implode('/', $studiengang->languages->pluck('display_name'))) ?>
                    </td>
                </tr>
            <? endif ?>
            <tr>
                <td>
                    <strong><?= _('Art der Zulassung') ?>:</strong>
                </td>
                <td>
                    <?= htmlReady($GLOBALS['MVV_STUDIENGANG']['ZULASSUNG']['values'][$studiengang->enroll]['name']) ?>
                </td>
            </tr>
            <? foreach ($studiengang->datafields as $df) : ?>
                <? if (mb_strpos($df->datafield->object_class, 'settings') !== false
                        && !$df->isNew()) : ?>
                    <? $tdf = $df->getTypedDatafield() ?>
                    <? if ($tdf->isVisible() && $tdf->getValue()) : ?>
                        <tr>
                            <td>
                                <strong><?= htmlReady($tdf->getName()) ?></strong>
                            </td>
                            <td>
                                <?= $tdf->getDisplayValue() ?>
                            </td>
                        </tr>
                    <? endif ?>
                <? endif ?>
            <? endforeach ?>
        </tbody>
    </table>
</article>
<? if (trim($studiengang->beschreibung)) : ?>
    <article class="studip toggle open">
        <header>
            <h1><a name=""><?= _('Beschreibung') ?></a></h1>
        </header>
        <section>
            <?= formatReady($studiengang->beschreibung) ?>
        </section>
    </article>
<? endif ?>
<? foreach ($studiengang->datafields as $df) : ?>
    <? if (mb_strpos($df->datafield->object_class, 'info') !== false
                        && !$df->isNew()) : ?>
        <? $tdf = $df->getTypedDatafield(); ?>
        <? if ($tdf->isVisible() && trim($tdf->getValue())) : ?>
            <article class="studip toggle open">
                <header>
                    <h1><a name=""><?= htmlReady($tdf->getName()) ?></a></h1>
                </header>
                <section>
                    <?= $tdf->getDisplayValue() ?>
                </section>
            </article>
        <? endif; ?>
    <? endif; ?>
<? endforeach; ?>
<? if (is_array($all_contacts) && count($all_contacts)) : ?>
    <article class="studip toggle open">
        <header>
            <h1><a name=""><?= _('Ihre AnsprechpartnerInnen') ?></a></h1>
        </header>
        <section>
            <? foreach ($all_contacts as $category => $contacts) : ?>
                <? if ($GLOBALS['MVV_STUDIENGANG']['PERSONEN_GRUPPEN']['values'][$category]['visible']) : ?>
                    <strong><?= htmlReady($GLOBALS['MVV_STUDIENGANG']['PERSONEN_GRUPPEN']['values'][$category]['name']) ?></strong>
                    <ul>
                        <? foreach ($contacts as $contact) : ?>
                        <li>
                            <?= htmlReady($contact['name']) ?>
                        </li>
                        <? endforeach; ?>
                    </ul>
                <? endif; ?>
            <? endforeach; ?>
        </section>
    </article>
<? endif ?>
<? if (is_array($all_documents) && count($all_documents)) : ?>
    <article class="studip toggle open">
        <header>
            <h1><a name=""><?= _('Dokumente: Ordnungen, Formulare, Informationen') ?></a></h1>
        </header>
        <section>
            <? foreach ($all_documents as $category => $files) : ?>
                <? if ($GLOBALS['MVV_DOCUMENTS']['CATEGORY']['values'][$category]['visible']) : ?>
                    <strong><?= htmlReady($GLOBALS['MVV_DOCUMENTS']['CATEGORY']['values'][$category]['name']) ?></strong>
                    <ul>
                        <? foreach ($files as $file) : ?>
                        <li>
                            <? if ($file['is_link']) : ?>
                                <a href="<?= $file['metadata_url'] ?>" target="_blank"><?= htmlReady($file['name']) ?></a>
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
<? endif ?>
<? if (count($all_aufbaustgs)) : ?>
    <article class="studip toggle open">
        <header>
            <h1><a name=""><?= _('Aufbau-/Kombinationsstudiengänge') ?></a></h1>
        </header>
        <section>
            <? foreach ($all_aufbaustgs as $typ => $aufbaustgs) : ?>
                <strong><?= htmlReady($GLOBALS['MVV_AUFBAUSTUDIENGANG']['TYP']['values'][$typ]['name']) ?></strong>
                <? if (count($aufbaustgs) > 4) : ?>
                    <input type="checkbox" class="mvv-cb-more-items" id="cb_more_aufbaustgs" checked>
                <? endif; ?>
                <ul>
                    <? foreach ($aufbaustgs as $i => $aufbaustg) : ?>
                    <li>
                        <?= htmlReady($aufbaustg->getDisplayName()) ?>
                        <? if ($i == 3) : ?>
                            <label class="cb-more-items" for="cb_more_aufbaustgs"><?= _('mehr...') ?></label>
                        <? endif; ?>
                    </li>
                    <? endforeach ?>
                </ul>
            <? endforeach ?>
        </section>
    </article>
<? endif; ?>
