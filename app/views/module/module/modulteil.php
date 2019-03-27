<? use Studip\Button, Studip\LinkButton; ?>
<?= $controller->jsUrl() ?>
<?php
$perm   = MvvPerm::get($modulteil);
$perm_d = MvvPerm::get($deskriptor);
if ($GLOBALS['MVV_MODULTEIL']['SPRACHE']['default'] != $display_language) {
    $perm_d->setVariant($display_language);
}
?>
<script>
    MVV.PARENT_ID = '<?= $modulteil->getId() ?>';
</script>

<form id="modulteil_form" class="default" action="<?= $controller->url_for('/modulteil/', $modulteil->id) ?>"
      method="post">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset id="mvv-field-modulteil-numbering">
        <legend><?= _('Nummerierung und Bezeichnung') ?></legend>
        <? if ($perm->haveFieldPerm('num_bezeichnung')): ?>
            <? if ($def_lang) : ?>
                <section class="hgroup">
                    <label id="mvv-field-modulteil-num_bezeichnung">
                        <?= _('Bezeichnung') ?>
                        <select name="num_bezeichnung">
                            <? $num_bezeichnung = $modulteil->isNew()
                                ? $GLOBALS['MVV_MODULTEIL']['NUM_BEZEICHNUNG']['default']
                                : $modulteil->num_bezeichnung; ?>
                            <option value=""><?= _('-- bitte wählen --') ?></option>
                            <? foreach ($GLOBALS['MVV_MODULTEIL']['NUM_BEZEICHNUNG']['values'] as $key => $value) : ?>
                                <? if ($value['visible']) : ?>
                                    <option value="<?= $key ?>"<?= $key === $num_bezeichnung ? ' selected' : '' ?>><?= htmlReady($value['name']) ?></option>
                                <? endif; ?>
                            <? endforeach; ?>
                        </select>
                    </label>
                    <label id="mvv-field-modulteil-nummer">
                        <?= _('Nummer') ?>
                        <input <?= $perm->disable('nummer') ?> type="text" name="nummer" id="nummer"
                                                               value="<?= htmlReady($modulteil->nummer) ?>" size="29">
                    </label>
                </section>
            <? else : ?>
                <? if ($modulteil->nummer || $modulteil->num_bezeichnung) : ?>
                    <?= $GLOBALS['MVV_MODULTEIL']['NUM_BEZEICHNUNG']['values'][$modulteil->num_bezeichnung]['name'] ?> <?= htmlReady($modulteil->nummer) ?>
                <? else : ?>
                    <?= _('Keine Angabe') ?>
                <? endif; ?>
            <? endif; ?>
        <? else: ?>
            <? if ($modulteil->nummer || $modulteil->num_bezeichnung) : ?>
                <?= $GLOBALS['MVV_MODULTEIL']['NUM_BEZEICHNUNG']['values'][$modulteil->num_bezeichnung]['name'] ?> <?= htmlReady($modulteil->nummer) ?>
            <? else : ?>
                <?= _('Keine Angabe') ?>
            <? endif; ?>
            <input type="hidden" name="num_bezeichnung" value="<?= $modulteil->num_bezeichnung ?>">
        <? endif; ?>
        <label id="mvv-field-modulteil-lernlehrform" for="lernlehrform"><?= _('Lern-/Lehrform') ?>
            <? if ($perm->haveFieldPerm('lernlehrform')): ?>
                <? if ($def_lang) : ?>
                    <select id="lernlehrform" name="lernlehrform">
                        <option value=""><?= _('-- bitte wählen --') ?></option>
                        <? foreach ($formen as $form_group) : ?>
                            <optgroup label="<?= htmlReady($form_group['group']['name']) ?>">
                                <? foreach ($form_group['options'] as $form) : ?>
                                    <option value="<?= $form['key'] ?>"<?= $form['key'] === $modulteil->lernlehrform ? ' selected' : '' ?>><?= $form['name'] ?></option>
                                <? endforeach; ?>
                            </optgroup>
                        <? endforeach; ?>
                    </select>
                <? else : ?>
                    <? if ($modulteil->lernlehrform) : ?>
                        <?= $GLOBALS['MVV_MODULTEIL']['LERNLEHRFORM']['values'][$modulteil->lernlehrform]['name'] ?>
                    <? else : ?>
                        <?= _('Keine Angabe') ?>
                    <? endif; ?>
                <? endif; ?>
            <? else: ?>
                <? if ($modulteil->lernlehrform) : ?>
                    <?= $GLOBALS['MVV_MODULTEIL']['LERNLEHRFORM']['values'][$modulteil->lernlehrform]['name'] ?>
                <? else : ?>
                    <?= _('Keine Angabe') ?>
                <? endif; ?>
                <input type="hidden" name="lernlehrform" value="<?= $modulteil->lernlehrform ?>">
            <? endif; ?>
        </label>
        <label id="mvv-field-modulteil-bezeichnung"><?= _('Zusätzliche Bezeichnung') ?>
            <input <?= $perm_d->disable('bezeichnung') ?>
                    type="text" name="bezeichnung" id="bezeichnung" value="<?= htmlReady($deskriptor->bezeichnung) ?>">
        </label>
    </fieldset>
    <fieldset id="mvv-field-modulteil-flexnow_modul">
        <legend><?= _('Modulteil-ID aus Fremdsystem') ?></legend>
        <label><?= _('ID') ?>
            <? if ($def_lang) : ?>
                <input <?= $perm->disable('flexnow_modul') ?>
                        type="text" name="flexnow_modul" id="flexnow_modul"
                        value="<?= htmlReady($modulteil->flexnow_modul) ?>"
                        maxlength="250">
            <? else : ?>
                <?= $modulteil->flexnow_modul ? htmlReady($modulteil->flexnow_modul) : _('Keine Angabe') ?>
            <? endif; ?>
        </label>
    </fieldset>
    <fieldset id="mvv-field-modulteil-semester">
        <legend><?= _('Häufigkeit/Turnus') ?></legend>
        <label><?= _('Häufigkeit') ?>
            <? $semester = $modulteil->semester ? $modulteil->semester
                : $GLOBALS['MVV_NAME_SEMESTER']['default']; ?>
            <? if ($perm->haveFieldPerm('semester')): ?>
                <? if ($def_lang) : ?>
                    <select id="mvv-semester" name="semester" size="1">
                        <? foreach ($GLOBALS['MVV_NAME_SEMESTER']['values'] as $key => $value) : ?>
                            <? if ($value['visible']) : ?>
                                <option value="<?= $key ?>"<?= $semester === $key ? ' selected' : '' ?>>
                                    <?= htmlReady($value['name']) ?>
                                </option>
                            <? endif; ?>
                        <? endforeach; ?>
                    </select>
                <? else : ?>
                    <?= htmlReady($GLOBALS['MVV_NAME_SEMESTER']['values'][$semester]['name']) ?>
                <? endif; ?>
            <? else: ?>
                <?= htmlReady($GLOBALS['MVV_NAME_SEMESTER']['values'][$semester]['name']) ?>
                <input type="hidden" name="semester" value="<?= $semester ?>">
            <? endif; ?>
        </label>
    </fieldset>
    <fieldset id="mvv-field-modulteil-languages">
        <legend><?= _('Unterrichtssprachen') ?></legend>
        <? if ($def_lang && $perm->haveFieldPerm('languages', MvvPerm::PERM_WRITE)) : ?>
            <ul id="language_target" class="mvv-assigned-items sortable mvv-languages">
                <? if (!count($modulteil->languages)) : ?>
                    <li class="mvv-item-list-placeholder"<?= (count($modulteil->languages) ? ' style="display:none;"' : '') ?>>
                        <?= _('Geben Sie die Unterrichtssprachen an.') ?>
                    </li>
                <? endif; ?>
                <? foreach ($modulteil->languages as $assigned_language) : ?>
                    <li id="language_<?= $assigned_language->lang ?>" class="sort_items">
                        <div class="mvv-item-list-text"><?= htmlReady($assigned_language->getDisplayName()) ?></div>
                        <div class="mvv-item-list-buttons">
                            <a href="#" class="mvv-item-remove">
                                <?= Icon::create('trash', ['title' => _('Sprache entfernen')])->asImg(); ?>
                            </a>
                        </div>
                        <input type="hidden" name="language_items[]" value="<?= $assigned_language->lang ?>">
                    </li>
                <? endforeach; ?>
            </ul>
            <?= $this->render_partial('shared/language_chooser', ['chooser_id' => 'language', 'chooser_languages' => $GLOBALS['MVV_MODULTEIL']['SPRACHE']['values']]); ?>
            <?= _('Die Reihenfolge der Sprachen kann durch Anklicken und Ziehen geändert werden.') ?>
        <? else : ?>
            <ul id="languages_target" class="mvv-assigned-items mvv-languages">
                <? if (count($modulteil->languages)) : ?>
                    <? foreach ($modulteil->languages as $assigned_language) : ?>
                        <li id="language_<?= $assigned_language->lang ?>">
                            <div class="mvv-item-list-text"><?= htmlReady($assigned_language->getDisplayName()) ?></div>
                            <input type="hidden" name="language_items[]" value="<?= $assigned_language->lang ?>">
                        </li>
                    <? endforeach; ?>
                <? else : ?>
                    <li class="mvv-item-list-placeholder">
                        <?= _('Es wurden noch keine Sprachen angegeben.') ?>
                    </li>
                <? endif; ?>
            </ul>
        <? endif; ?>
    </fieldset>
    <fieldset>
        <legend><?= _('Teilnahmevoraussetzung Modulteil') ?></legend>
        <label><?= _('Teilnahmevoraussetzung') ?>
            <? if ($perm_d->haveFieldPerm('voraussetzung', MvvPerm::PERM_WRITE)) : ?>
                <textarea cols="60" rows="5" name="voraussetzung" id="voraussetzung"
                          class="add_toolbar ui-resizable wysiwyg"><?= htmlReady($deskriptor->voraussetzung) ?></textarea>
            <? else : ?>
                <textarea readonly cols="60" rows="5" name="voraussetzung" id="voraussetzung"
                          class="ui-resizable"><?= htmlReady($deskriptor->voraussetzung) ?></textarea>
            <? endif; ?>
        </label>
    </fieldset>
    <fieldset>
        <legend><?= _('Kommentar Modulteil') ?></legend>
        <label><?= _('Kommentar') ?>
            <? if ($perm_d->haveFieldPerm('kommentar', MvvPerm::PERM_WRITE)) : ?>
                <textarea cols="60" rows="5" name="kommentar" id="kommentar"
                          class="add_toolbar ui-resizable wysiwyg"><?= htmlReady($deskriptor->kommentar) ?></textarea>
            <? else : ?>
                <textarea readonly cols="60" rows="5" name="kommentar" id="kommentar"
                          class="ui-resizable"><?= htmlReady($deskriptor->kommentar) ?></textarea>
            <? endif; ?>
        </label>
    </fieldset>
    <fieldset>
        <legend><?= _('Kapazität/Teilnahmezahl Modulteil') ?></legend>
        <section id="mvv-field-modulteil-kapazitaet" class="hgroup size-m">
            <? if ($perm->haveFieldPerm('kapazitaet') && $def_lang): ?>
                <label><?= _('Teilnahmezahl') ?>
                    <input type="text" name="kapazitaet" id="kapazitaet"
                           value="<?= htmlReady($modulteil->kapazitaet) ?>" <?= $modulteil->kapazitaet === '' ? ' disabled' : ''; ?>>
                </label>
                <label>
                    <input type="checkbox" name="kap_unbegrenzt" id="kap_unbegrenzt"
                           value="1"<?= $modulteil->kapazitaet === '' ? ' checked' : ''; ?>
                           onchange="jQuery('#kapazitaet').attr('disabled', function(foo, attr){ jQuery(this).val(attr ? '0' : ''); return !attr; }); return false;">
                    <?= _('unbegrenzt') ?>
                </label>
            <? else: ?>
                <?= _('Teilnahmezahl') ?>: <?= $modulteil->kapazitaet === '' ? _('unbegrenzt') : htmlReady($modulteil->kapazitaet) ?>
                <input type="hidden" name="kapazitaet" value="<?= htmlReady($modulteil->kapazitaet) ?>">
                <input type="hidden" name="kap_unbegrenzt" value="<?= $modulteil->kapazitaet === '' ? '1' : ''; ?>">
            <? endif; ?>
        </section>
        <label id="mvv-field-modulteil-kommentar_kapazitaet"><?= _('Kommentar') ?>
            <? if ($perm_d->haveFieldPerm('kommentar_kapazitaet', MvvPerm::PERM_WRITE)) : ?>
                <textarea cols="60" rows="5" name="kommentar_kapazitaet" id="kommentar_kapazitaet"
                          class="add_toolbar ui-resizable wysiwyg"><?= htmlReady($deskriptor->kommentar_kapazitaet) ?></textarea>
            <? else : ?>
                <textarea readonly cols="60" rows="5" name="kommentar_kapazitaet" id="kommentar_kapazitaet"
                          class="ui-resizable"><?= htmlReady($deskriptor->kommentar_kapazitaet) ?></textarea>
            <? endif; ?>
        </label>
    </fieldset>
    <fieldset>
        <legend><?= _('Semesterwochenstunden') ?></legend>
        <label><?= _('SWS') ?>
            <? if ($def_lang) : ?>
                <input <?= $perm->disable('sws') ?>
                        type="text" name="sws" id="sws" value="<?= htmlReady($modulteil->sws) ?>">
            <? else : ?>
                <?= $modulteil->sws ? htmlReady($modulteil->sws) : _('keine Angabe') ?>
            <? endif; ?>
        </label>
    </fieldset>
    <fieldset>
        <legend><?= _('Kreditpunkte') ?></legend>
        <? if ($def_lang) : ?>
            <input <?= $perm->disable('kp') ?>
                    type="text" name="kp" id="kp" value="<?= htmlReady($modulteil->kp) ?>" maxlength="2">
        <? else : ?>
            <?= $modulteil->kp ? htmlReady($modulteil->kp) : _('keine Angabe') ?>
        <? endif; ?>
    </fieldset>
    <fieldset>
        <legend><?= _('Workload Präsenzzeit') ?></legend>
        <label><?= _('Workload') ?>
            <? if ($def_lang) : ?>
                <input <?= $perm->disable('wl_praesenz') ?>
                        type="text" name="wl_praesenz" id="wl_praesenz"
                        value="<?= htmlReady($modulteil->wl_praesenz) ?>" size="4"
                        maxlength="4">
            <? else : ?>
                <?= $modulteil->wl_praesenz ? htmlReady($modulteil->wl_praesenz) : _('keine Angabe') ?>
            <? endif; ?>
        </label>
        <label for="kommentar_wl_praesenz" style="vertical-align: top;"><?= _('Kommentar') ?>
            <? if ($perm_d->haveFieldPerm('kommentar_wl_praesenz', MvvPerm::PERM_WRITE)) : ?>
                <textarea cols="60" rows="5" name="kommentar_wl_praesenz" id="kommentar_wl_praesenz"
                          class="add_toolbar ui-resizable wysiwyg"><?= htmlReady($deskriptor->kommentar_wl_praesenz) ?></textarea>
            <? else : ?>
                <textarea readonly cols="60" rows="5" name="kommentar_wl_praesenz" id="kommentar_wl_praesenz"
                          class="ui-resizable"><?= htmlReady($deskriptor->kommentar_wl_praesenz) ?></textarea>
            <? endif; ?>
        </label>
    </fieldset>
    <fieldset>
        <legend><?= _('Workload Vor-/Nachbereitung') ?></legend>
        <label><?= _('Workload') ?>
            <? if ($def_lang) : ?>
                <input <?= $perm->disable('wl_bereitung') ?>
                        type="text" name="wl_bereitung" id="wl_bereitung"
                        value="<?= htmlReady($modulteil->wl_bereitung) ?>" size="4"
                        maxlength="4">
            <? else : ?>
                <?= $modulteil->wl_bereitung ? htmlReady($modulteil->wl_bereitung) : _('keine Angabe') ?>
            <? endif; ?>
        </label>
        <label><?= _('Kommentar') ?>
            <? if ($perm_d->haveFieldPerm('kommentar_wl_bereitung', MvvPerm::PERM_WRITE)) : ?>
                <textarea cols="60" rows="5" name="kommentar_wl_bereitung" id="kommentar_wl_bereitung"
                          class="add_toolbar ui-resizable wysiwyg"><?= htmlReady($deskriptor->kommentar_wl_bereitung) ?></textarea>
            <? else : ?>
                <textarea readonly cols="60" rows="5" name="kommentar_wl_bereitung" id="kommentar_wl_bereitung"
                          class="ui-resizable"><?= htmlReady($deskriptor->kommentar_wl_bereitung) ?></textarea>
            <? endif; ?>
        </label>
    </fieldset>
    <fieldset>
        <legend><?= _('Workload Modulteil selbstgestaltete Arbeitszeit') ?></legend>
        <label><?= _('Workload') ?>
            <? if ($def_lang) : ?>
                <input <?= $perm->disable("wl_selbst") ?>
                        type="text" name="wl_selbst" id="wl_selbst"
                        value="<?= htmlReady($modulteil->wl_selbst) ?>" size="4"
                        maxlength="4">
            <? else : ?>
                <?= $modulteil->wl_selbst ? htmlReady($modulteil->wl_selbst) : _('keine Angabe') ?>
            <? endif; ?>
        </label>
        <label><?= _('Kommentar') ?>
            <? if ($perm_d->haveFieldPerm('kommentar_wl_selbst', MvvPerm::PERM_WRITE)) : ?>
                <textarea cols="60" rows="5" name="kommentar_wl_selbst" id="kommentar_wl_selbst"
                          class="add_toolbar ui-resizable wysiwyg"><?= htmlReady($deskriptor->kommentar_wl_selbst) ?></textarea>
            <? else : ?>
                <textarea readonly cols="60" rows="5" name="kommentar_wl_selbst" id="kommentar_wl_selbst"
                          class="ui-resizable"><?= htmlReady($deskriptor->kommentar_wl_selbst) ?></textarea>
            <? endif; ?>
        </label>
    </fieldset>
    <fieldset>
        <legend><?= _('Workload Modulteil Prüfung') ?></legend>
        <label><?= _('Workload') ?>
            <? if ($def_lang) : ?>
                <input <?= $perm->disable('wl_pruef') ?> type="text" name="wl_pruef" id="wl_pruef"
                                                         value="<?= htmlReady($modulteil->wl_pruef) ?>" maxlength="4">
            <? else : ?>
                <?= $modulteil->wl_pruef ? htmlReady($modulteil->wl_pruef) : _('keine Angabe') ?>
            <? endif; ?>
        </label>
        <label><?= _('Kommentar') ?>
            <? if ($perm_d->haveFieldPerm('kommentar_wl_pruef', MvvPerm::PERM_WRITE)) : ?>
                <textarea cols="60" rows="5" name="kommentar_wl_pruef" id="kommentar_wl_pruef"
                          class="add_toolbar ui-resizable wysiwyg"><?= htmlReady($deskriptor->kommentar_wl_pruef) ?></textarea>
            <? else : ?>
                <textarea readonly cols="60" rows="5" name="kommentar_wl_pruef" id="kommentar_wl_pruef"
                          class="ui-resizable"><?= htmlReady($deskriptor->kommentar_wl_pruef) ?></textarea>
            <? endif; ?>
        </label>
    </fieldset>
    <fieldset>
        <legend><?= _('Prüfung/Note') ?></legend>
        <label><?= _('Prüfungsvorleistung') ?>
            <? if ($perm_d->haveFieldPerm('pruef_vorleistung', MvvPerm::PERM_WRITE)) : ?>
                <textarea cols="60" rows="5" name="pruef_vorleistung" id="pruef_vorleistung"
                          class="add_toolbar ui-resizable wysiwyg"><?= htmlReady($deskriptor->pruef_vorleistung) ?></textarea>
            <? else : ?>
                <textarea readonly cols="60" rows="5" name="pruef_vorleistung" id="pruef_vorleistung"
                          class="ui-resizable"><?= htmlReady($deskriptor->pruef_vorleistung) ?></textarea>
            <? endif; ?>
        </label>
        <label><?= _('Prüfungsleistung Modulteil') ?>
            <? if ($perm_d->haveFieldPerm('pruef_leistung', MvvPerm::PERM_WRITE)) : ?>
                <textarea cols="60" rows="5" name="pruef_leistung" id="pruef_leistung"
                          class="add_toolbar ui-resizable wysiwyg"><?= htmlReady($deskriptor->pruef_leistung) ?></textarea>
            <? else : ?>
                <textarea readonly cols="60" rows="5" name="pruef_leistung" id="pruef_leistung"
                          class="ui-resizable"><?= htmlReady($deskriptor->pruef_leistung) ?></textarea>
            <? endif; ?>
        </label>
        <label><?= _('Anteil an Modulnote') ?>
            <? if ($def_lang) : ?>
                <input <?= $perm->disable('anteil_note') ?> type="text" name="anteil_note" id="anteil_note"
                                                            value="<?= htmlReady($modulteil->anteil_note) ?>" size="29">
            <? else : ?>
                <?= $modulteil->anteil_note ? htmlReady($modulteil->anteil_note) : _('Keine Angabe') ?>
            <? endif; ?>
        </label>
        <label><?= _('Ausgleichbar bei Minderleistung') ?>
            <? if ($def_lang && $perm->haveFieldPerm('ausgleichbar', MvvPerm::PERM_WRITE)) : ?>
                <section class="hgroup">
                    <label>
                        <input type="radio" id="ausgleichbar" name="ausgleichbar"
                               value="0"<?= $modulteil->ausgleichbar === 0 ? ' checked' : '' ?>>
                        <?= _('Nein') ?>
                    </label>
                    <label>
                        <input type="radio" name="ausgleichbar"
                               value="1"<?= $modulteil->ausgleichbar === 1 ? ' checked' : '' ?>>
                        <?= _('Ja') ?>
                    </label>
                </section>
            <? else : ?>
                <? if ($modulteil->ausgleichbar) : ?>
                    <?= _('Dieser Modulteil <strong>ist ausgleichbar</strong> bei Minderleistung.') ?>
                <? else : ?>
                    <?= _('Dieser Modulteil ist <strong>nicht ausgleichbar</strong> bei Minderleistung.') ?>
                <? endif; ?>
            <? endif; ?>
        </label>
    </fieldset>
    <fieldset>
        <legend><?= _('Anwesenheitspflicht') ?></legend>
        <? if ($def_lang && $perm->haveFieldPerm('pflicht', MvvPerm::PERM_WRITE)) : ?>
            <section class="hgroup">
                <label>
                    <input type="radio" id="pflicht" name="pflicht"
                           value="0"<?= $modulteil->pflicht === 0 ? ' checked' : '' ?>>
                    <?= _('Nein') ?>
                </label>
                <label>
                    <input type="radio" name="pflicht" value="1"<?= $modulteil->pflicht === 1 ? ' checked' : '' ?>>
                    <?= _('Ja') ?>
                </label>
            </section>
        <? else : ?>
            <? if ($modulteil->pflicht) : ?>
                <?= _('Es besteht <strong>Anwesenheitspflicht</strong>.') ?>
            <? else : ?>
                <?= _('Es besteht <strong>keine Anwesenheitspflicht</strong>.') ?>
            <? endif; ?>
        <? endif; ?>
        <label><?= _('Kommentar') ?>
            <? if ($perm_d->haveFieldPerm('kommentar_pflicht', MvvPerm::PERM_WRITE)) : ?>
                <textarea cols="60" rows="5" name="kommentar_pflicht" id="kommentar_pflicht"
                          class="add_toolbar ui-resizable wysiwyg"><?= htmlReady($deskriptor->kommentar_pflicht) ?>
                </textarea>
            <? else : ?>
                <textarea readonly cols="60" rows="5" name="kommentar_pflicht" id="kommentar_pflicht"
                          class="ui-resizable"><?= htmlReady($deskriptor->kommentar_pflicht) ?>
                </textarea>
            <? endif; ?>
        </label>
    </fieldset>
    <? if (count($deskriptor->datafields)) : ?>
        <fieldset>
            <legend><?= _('Weitere Angaben') ?></legend>
            <? foreach ($deskriptor->datafields as $entry) : ?>
                <? if ($entry->lang === '') : ?>
                    <? if (!$def_lang) : ?>
                        <? $df = new DatafieldEntryModel(
                            [
                                $entry->datafield_id,
                                $entry->range_id,
                                $entry->sec_range_id,
                                $language === 'de_DE' ? '' : $language
                            ]); ?>
                    <? else : ?>
                        <? $df = $entry; ?>
                    <? endif; ?>
                    <? $tdf = $df->getTypedDatafield(); ?>
                    <? if ($perm_d->haveDfEntryPerm($df, MvvPerm::PERM_WRITE)) : ?>
                        <?= $tdf->getHTML('datafields'); ?>
                    <? else : ?>
                        <em><?= htmlReady($tdf->getName()) ?>:</em><br>
                        <?= $tdf->getDisplayValue() ?>
                    <? endif; ?>
                <? endif; ?>
            <? endforeach; ?>
        </fieldset>
    <? endif; ?>
    <input type="hidden" name="modul_id" value="<?= htmlReady($modulteil->modul_id) ?>">
    <input type="hidden" name="display_language" value="<?= $display_language ?>">
    <footer>
        <? if ($deskriptor->isNew()) : ?>
            <?= Button::createAccept(_('Anlegen'), 'store', ['title' => _('Modulteil anlegen')]) ?>
        <? else : ?>
            <?= Button::createAccept(_('Übernehmen'), 'store', ['title' => _('Änderungen übernehmen')]) ?>
            <? if (!$def_lang && !$deskriptor->isNew() && in_array($display_language, $translations)) : ?>
                <?= Button::create(
                    _('Löschen'),
                    'delete',
                    ['title'      => _('Deskriptor löschen'), 'data-confirm' => sprintf(_('Soll der Deskriptor in der Ausgabesprache %s gelöscht werden?'), $GLOBALS['MVV_LANGUAGES']['values'][$display_language]['name']),
                     'formaction' => $controller->url_for('/delete_modulteil_deskriptor', $deskriptor->id, $display_language)
                    ]
                ); ?>s
            <? endif; ?>
        <? endif; ?>
        <?= LinkButton::createCancel(_('Abbrechen'), $cancel_url, ['title' => _('zurück zur Übersicht')]) ?>
    </footer>
</form>
<? if (!$def_lang) : ?>
    <script>
        jQuery('#modulteil_form').find('textarea, input[type=text]').after('<div style="padding-top:10px;"><a href="#" title="<?= _('Originalfassung anzeigen') ?>" class="mvv-show-original" data-type="modulteil"><img src="<?= Assets::image_path('languages/lang_' . mb_strtolower($modul->getDefaultLanguage()) . '.gif') ?>" alt="<?= _('Originalfassung') ?>"></a></div>');
    </script>
<? endif; ?>
