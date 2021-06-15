<? use Studip\Button, Studip\LinkButton; ?>
<?= $controller->jsUrl() ?>
<? $perm = MvvPerm::get($studiengang) ?>
<form class="default" action="<?= $controller->link_for('/studiengang', $studiengang->id) ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset class="collapsable">
        <legend>
            <?= _('Fach-Abschluss-Bezeichnung'); ?>
        </legend>

        <? if ($perm->haveFieldPerm('name', MvvPerm::PERM_WRITE)) : ?>
            <label>
                <?= _('Fachsuche:') ?>
                <?= tooltipHtmlIcon(_('Soll der Name des Studiengangs mit dem eines Fachs übereinstimmen, geben Sie den Namen des Fachs ein, und wählen Sie das Fach aus der Liste. Es werden dann automatisch die weiteren Bezeichnungen aus den Daten des Fachs übernommen.')) ?>
                <?= $search ?>
            </label>
        <? endif; ?>
        <label>
            <?= _('Name:') ?>
            <?= MvvI18N::input('name', $studiengang->name, ['maxlength' => '255'])->checkPermission($studiengang) ?>
        </label>
        <label><?= _('Kurzbezeichnung:') ?>
            <?= MvvI18N::input('name_kurz', $studiengang->name_kurz, ['maxlength' => '50'])->checkPermission($studiengang) ?>
        </label>
        <label><?= _('Abschlusszuordnung:') ?>
        <? if ($perm->haveFieldPerm('abschluss_id')) : ?>
        <select id="abschluss_id" name="abschluss_id" size="1">
            <option value=""><?= _('-- bitte wählen --') ?></option>
            <? foreach ($abschluesse as $abschluss) : ?>
            <option <?= ($abschluss['abschluss_id'] == $studiengang->abschluss_id ? 'selected ' : '') ?>value="<?= $abschluss['abschluss_id'] ?>"><?= htmlReady($abschluss['name']) ?></option>
            <? endforeach; ?>
        </select>
        <? else: ?>
            <? $abschluss = Abschluss::find($studiengang->abschluss_id)?>
            <?= htmlReady($abschluss['name']) ?>
            <input type="hidden" name="abschluss_id" value="<?= $studiengang->abschluss_id ?>">
        <? endif; ?>
        </label>
        <label for="mvv-abschlussgrad"><?= _('Angestrebter Abschlussgrad') ?>
            <input type ="hidden" name="abschlussgrad" value="<?= $studiengang->abschlussgrad ?>">
            <select id="mvv-abschlussgrad" name="abschlussgrad"<?= $perm->haveFieldPerm('abschlussgrad', MvvPerm::PERM_WRITE) ? '' : ' disabled' ?>>
            <? foreach ($GLOBALS['MVV_STUDIENGANG']['ABSCHLUSSGRAD']['values'] as $key => $entry) : ?>
                <? if ($entry['visible']) : ?>
                <option value="<?= htmlReady($key) ?>"<?= $key == $studiengang->abschlussgrad ? ' selected' : '' ?>><?= htmlReady($entry['name']) ?></option>
                <? endif; ?>
            <? endforeach; ?>
            </select>
        </label>
    </fieldset>

    <fieldset class="collapsable collapsed">
        <legend>
            <?= _('Gültigkeit'); ?>
        </legend>

        <label>
            <?= _('von Semester:') ?>
            <? if ($perm->haveFieldPerm('start')) : ?>
                <select name="start" size="1">
                    <option value=""><?= _('-- Semester wählen --') ?></option>
                    <? foreach ($semester as $sem) : ?>
                        <option value="<?= $sem->semester_id ?>"<?= ($sem->semester_id === $studiengang->start ? ' selected' : '') ?>>
                            <?= htmlReady($sem->name) ?>
                        </option>
                    <? endforeach; ?>
                </select>
            <? else : ?>
                <? $sem = Semester::find($studiengang->start) ?>
                <?= htmlReady($sem->name) ?>
                <input type="hidden" name="start" value="<?= $studiengang->start ?>">
            <? endif; ?>
        </label>
        <label>
            <?= _('bis Semester:') ?>
            <? if ($perm->haveFieldPerm('end')) : ?>
                <select name="end" size="1">
                    <option value=""><?= _('unbegrenzt gültig') ?></option>
                    <? foreach ($semester as $sem) : ?>
                        <option value="<?= $sem->semester_id ?>"<?= ($sem->semester_id === $studiengang->end ? ' selected' : '') ?>>
                            <?= htmlReady($sem->name) ?>
                        </option>
                    <? endforeach; ?>
                </select>
            <? else : ?>
                <? if ($studiengang->end !== '') : ?>
                    <? $sem = Semester::find($studiengang->end) ?>
                    <?= htmlReady($sem->name) ?>
                <? else : ?>
                    <?= _('unbegrenzt gültig') ?>
                <? endif; ?>
                <input type="hidden" name="end" value="<?= $studiengang->end ?>">
            <? endif; ?>
        </label>
        <div><?= _('Das Endsemester wird nur angegeben, wenn der Studiengang abgeschlossen ist.') ?></div>
        <label>
            <?= _('Beschlussdatum:') ?>
            <? if ($perm->haveFieldPerm('beschlussdatum')) : ?>
                <input type="text" name="beschlussdatum"
                       value="<?= ($studiengang->beschlussdatum ? strftime('%d.%m.%Y', $studiengang->beschlussdatum) : '') ?>"
                       placeholder="<?= _('TT.MM.JJJJ') ?>" size="15" class="with-datepicker">
            <? else : ?>
                <?= ($studiengang->beschlussdatum ? strftime('%d.%m.%Y', $studiengang->beschlussdatum) : '') ?>
                <input type="hidden" name="beschlussdatum"
                       value="<?= ($studiengang->beschlussdatum ? strftime('%d.%m.%Y', $studiengang->beschlussdatum) : '') ?>">
            <? endif; ?>
        </label>
        <label>
            <?= _('Fassung:') ?>
            <select <?= $perm->disable('fassung_nr') ?> name="fassung_nr" style="display: inline-block; width: 5em;">
                <option value="">--</option>
                <? foreach (range(1, 30) as $nr) : ?>
                    <option<?= $nr === (int)$studiengang->fassung_nr ? ' selected' : '' ?> value="<?= $nr ?>"><?= $nr ?>.</option>
                <? endforeach; ?>
            </select>
            <? if ($perm->haveFieldPerm('fassung_typ')): ?>
                <select style="display: inline-block; max-width: 40em;" name="fassung_typ">
                    <option value="0">--</option>
                    <? foreach ($GLOBALS['MVV_STUDIENGANG']['FASSUNG_TYP'] as $key => $entry) : ?>
                        <option value="<?= $key ?>"<?= $key === $studiengang->fassung_typ ? ' selected' : '' ?>><?= htmlReady($entry['name']) ?></option>
                    <? endforeach; ?>
                </select>
            <? else: ?>
                <?= ($studiengang->fassung_typ === '0' ? '--' : $GLOBALS['MVV_STUDIENGANG']['FASSUNG_TYP'][$studiengang->fassung_typ]['name']) ?>
                <input type="hidden" name="fassung_typ" value="<?= $studiengang->fassung_typ ?>">
            <? endif; ?>
        </label>
    </fieldset>

    <fieldset class="collapsable collapsed">
        <legend>
            <?= _('Einstellungen'); ?>
        </legend>

        <label>
            <?= _('Verantwortliche Einrichtung') ?>
        <? if ($perm->haveFieldPerm('institut_id', MvvPerm::PERM_WRITE)): ?>
            <div>
                <div style="display: inline-block;width: 100%;max-width: 48em;">
                    <?= $search_institutes->render() ?>
                </div>
            <? if (Request::submitted('search_institutes')) : ?>
                <?= Icon::create('decline')->asInput(['name' => 'reset_institutes', 'data-qs_id' => $search_institutes_id, 'class' => 'text-bottom']) ?>
            <? else : ?>
                <?= Icon::create('search')->asInput(['name' => 'search_institutes', 'data-qs_id' => $search_institutes_id, 'data-qs_name' => $search_institutes->getId(), 'class' => 'mvv-qs-button text-bottom']) ?>
            <? endif; ?>
            </div>
        <? endif; ?>
            <ul id="institut_target" class="mvv-assigned-items mvv-assign-single mvv-institute">
                <li class="mvv-item-list-placeholder"<?= ($studiengang->institut_id ? ' style="display: none;"' : '') ?>><?= _('Bitte eine Einrichtung suchen und zuordnen.') ?></li>
                <? if ($studiengang->institut_id) : ?>
                <li id="institut_<?= $studiengang->institut_id ?>">
                    <div class="mvv-item-list-text">
                        <? if ($institut) : ?>
                            <?= htmlReady($institut->getDisplayName()) ?>
                        <? else: ?>
                            <?= _('Unbekannte Einrichtung') ?>
                        <? endif; ?>
                    </div>
                    <? if ($perm->haveFieldPerm('institut_id', MvvPerm::PERM_WRITE)): ?>
                        <div class="mvv-item-list-buttons">
                            <a href="#"
                               class="mvv-item-remove"><?= Icon::create('trash', Icon::ROLE_CLICKABLE , ['title' => _('Einrichtung entfernen')])->asImg(); ?></a>
                        </div>
                    <? endif; ?>
                    <input type="hidden" name="institut_item" value="<?= $studiengang->institut_id ?>">
                </li>
                <? endif; ?>
            </ul>
        </label>


        <label><?= _('Status der Bearbeitung') ?></label>
        <? if ($perm->haveFieldPerm('stat', MvvPerm::PERM_WRITE) && $studiengang->stat !== 'planung'): ?>
            <? foreach ($GLOBALS['MVV_STUDIENGANG']['STATUS']['values'] as $key => $status_bearbeitung) : ?>
                <label>
                    <input <?= ($studiengang->stat === 'ausgelaufen' && $key === 'genehmigt') ? 'disabled' : '' ?>
                            type="radio" name="status"
                            value="<?= $key ?>"<?= $studiengang->stat === $key ? ' checked' : '' ?>>
                    <?= $status_bearbeitung['name'] ?>
                </label>
            <? endforeach; ?>
            <? else : ?>
            <label>
                <?= $GLOBALS['MVV_STUDIENGANG']['STATUS']['values'][$studiengang->stat]['name'] ?>
                <input type="hidden" name="status" value="<?= $studiengang->stat ?>">
            </label>
        <? endif; ?>

        <label for="kommentar_status" style="vertical-align: top;"><?= _('Kommentar Bearbeitungsstatus') ?>
            <? if ($perm->haveFieldPerm('kommentar_status', MvvPerm::PERM_WRITE)): ?>
                <textarea cols="60" rows="5" name="kommentar_status" id="kommentar_status"
                      class="add_toolbar ui-resizable wysiwyg"><?= htmlReady($studiengang->kommentar_status) ?></textarea>
            <? else: ?>
                <textarea readonly cols="60" rows="5" name="kommentar_status" id="kommentar_status"
                      class="ui-resizable"><?= htmlReady($studiengang->kommentar_status) ?></textarea>
            <? endif; ?>
        </label>

        <input type="hidden" name="stg_typ" value="<?= htmlReady($studiengang->typ) ?>">
        <label><?= _('Studiengangteile') ?></label>
        <label>
            <input id="stg_typ" type="radio" name="stg_typ"<?= ($studiengang->typ != 'mehrfach' ? ' checked' : '') ?> value="einfach"<?= $perm->disable('typ', MvvPerm::PERM_WRITE) ?>>
            <?= _('Einfach-Studiengang (Diesem Studiengang wird ein oder mehrere Studiengangteil(e) direkt zugewiesen)') ?>
        </label>
        <label>
            <input type="radio" name="stg_typ"<?= ($studiengang->typ == 'mehrfach' ? ' checked' : '') ?> value="mehrfach"<?= $perm->disable('typ', MvvPerm::PERM_WRITE) ?>>
            <?= _('Mehrfach-Studiengang (Diesem Studiengang können mehrere Studiengangteile in Abschnitten zugewiesen werden)') ?>
        </label>

        <label><?= _('Regelstudienzeit') ?>
            <input type="number" name="studienzeit" value="<?= htmlReady($studiengang->studienzeit) ?>"<?= $perm->disable('studienzeit', MvvPerm::PERM_WRITE) ?>>
        </label>

        <label><?= _('Studienplätze') ?>
            <input type="number" name="studienplaetze" value="<?= htmlReady($studiengang->studienplaetze) ?>"<?= $perm->disable('studienplaetze', MvvPerm::PERM_WRITE) ?>>
        </label>

        <label for="mvv-language-chooser-select"><?= _('Lehrsprachen') ?>
        <? if ($perm->haveFieldPerm('languages', MvvPerm::PERM_CREATE)) : ?>
            <ul id="language_target" class="mvv-assigned-items sortable mvv-languages">
                <li class="mvv-item-list-placeholder"<?= (count($studiengang->languages) ? ' style="display:none;"' : '') ?>>
                    <?= _('Geben Sie die Lehrsprachen an.') ?>
                </li>
                <? foreach ($studiengang->languages as $assigned_language) : ?>
                <li id="language_<?= $assigned_language->lang ?>" class="sort_items">
                    <div class="mvv-item-list-text"><?= htmlReady($assigned_language->getDisplayName()) ?></div>
                    <div class="mvv-item-list-buttons">
                        <a href="#" class="mvv-item-remove"><?= Icon::create('trash', 'clickable', array('title' => _('Sprache entfernen')))->asImg(); ?></a>
                    </div>
                    <input type="hidden" name="language_items[]" value="<?= htmlReady($assigned_language->lang) ?>">
                </li>
                <? endforeach; ?>
            </ul>
            <?= $this->render_partial('shared/language_chooser', ['chooser_id' => 'language', 'chooser_languages' => $GLOBALS['MVV_STUDIENGANG']['SPRACHE']['values'], 'addition' => _('Die Reihenfolge der Sprachen kann durch Anklicken und Ziehen geändert werden.')]); ?>
        <? else : ?>
            <ul id="languages_target" class="mvv-assigned-items mvv-languages">
            <? if (count($studiengang->languages)) : ?>
                <? foreach ($studiengang->languages as $assigned_language) : ?>
                <li id="institut_<?= $assigned_language->lang ?>">
                    <div class="mvv-item-list-text"><?= htmlReady($assigned_language->getDisplayName()) ?></div>
                    <input type="hidden" name="language_items[]" value="<?= htmlReady($assigned_language->lang) ?>">
                </li>
                <? endforeach; ?>
            <? else : ?>
                <li class="mvv-item-list-placeholder">
                    <?= _('Es wurden noch keine Sprachen angegeben.') ?>
                </li>
            <? endif; ?>
            </ul>
        <? endif; ?>
        </label>
        <label for="mvv-studycourse-types" style="margin-bottom: 0ex;"><?= _('Typ des Studiengangs') ?></label>
        <? if ($perm->haveFieldPerm('studycourse_types', MvvPerm::PERM_CREATE)) : ?>
            <select id="mvv-studycourse-types" name="studycourse_types[]" class="nested_select" multiple>
        <? else : ?>
            <? foreach ($studiengang->studycourse_types as $stc_type) : ?>
                <input type="hidden" name="studycourse_types[]" value="<?= $stc_type->type ?>">
            <? endforeach; ?>
            <select id="mvv-studycourse-types" name="studycourse_types[]" class="nested_select" disabled multiple>
        <? endif; ?>
        <? foreach ($GLOBALS['MVV_STUDIENGANG']['STUDYCOURSE_TYPE']['values'] as $key => $entry) : ?>
            <? if ($entry['visible']) : ?>
                <option value="<?= $key ?>"<?= in_array($key, $studiengang->studycourse_types->pluck('type')) ? ' selected' : '' ?>><?= htmlReady($entry['name']) ?></option>
            <? endif; ?>
        <? endforeach; ?>
        </select>
        <label style="margin-top: 1.5ex;">
            <input type="checkbox" name="enroll[]" value="wise"<?= strpos($studiengang->enroll, 'wise') !== false ? ' checked' : '' ?><?= $perm->disable('enroll', MvvPerm::PERM_WRITE) ?>>
            <?= _('Bewerbung/Einschreibung im Wintersemester möglich') ?>
        </label>
        <label>
            <input type="checkbox" name="enroll[]" value="sose"<?= strpos($studiengang->enroll, 'sose') !== false ? ' checked' : '' ?><?= $perm->disable('enroll', MvvPerm::PERM_WRITE) ?>>
            <?= _('Bewerbung/Einschreibung im Sommersemester möglich') ?>
        </label>

        <div id="mvv-aufbaustg-table"></div>

        <? $datafields = DataFieldEntry::getDataFieldEntries($studiengang->id, 'studycourse'); ?>
        <? foreach ($datafields as $df) : ?>
            <? if (mb_strpos($df->model->object_class, 'settings') !== false) : ?>
                <? if ($perm->haveDfEntryPerm($df->model->id, MvvPerm::PERM_WRITE)) : ?>
                    <?= $df->getHTML('datafields'); ?>
                <? else : ?>
                    <em><?= htmlReady($df->getName()) ?>:</em><br>
                    <?= $df->getDisplayValue() ?>
                <? endif; ?>
            <? endif; ?>
        <? endforeach; ?>
    </fieldset>

    <fieldset class="collapsable collapsed">
        <legend>
            <?= _('Inhalte und Informationen'); ?>
        </legend>
        <label>
            <?= _('Beschreibung') ?>
            <?= MvvI18N::textarea('beschreibung', $studiengang->beschreibung, ['class' => 'add_toolbar ui-resizable wysiwyg'])->checkPermission($studiengang) ?>
        </label>
        <? foreach ($datafields as $df) : ?>
            <? if (mb_strpos($df->model->object_class, 'info') !== false) : ?>
                <? if ($perm->haveDfEntryPerm($df->model->id, MvvPerm::PERM_WRITE)) : ?>
                    <?= $df->getHTML('datafields'); ?>
                <? else : ?>
                    <em><?= htmlReady($df->getName()) ?>:</em><br>
                    <?= $df->getDisplayValue() ?>
                <? endif; ?>
            <? endif; ?>
        <? endforeach; ?>
        <label><?= _('Schlagwörter') ?>
        <textarea <?= $perm->disable('schlagworte') ?> cols="60" rows="5" name="schlagworte" id="schlagworte" class="ui-resizable"><?= htmlReady($studiengang->schlagworte) ?></textarea>
        <div><?= _('Hier können zusätzlich Schlagwörter angegeben werden, die in der Suche berücksichtigt werden.') ?></div>
        </label>
    </fieldset>

    <fieldset class="collapsable collapsed">
        <legend>
            <?= _('Dokumente'); ?>
        </legend>
        <?= $this->render_partial('materialien/files/range', ['perm_dokumente' => $perm->haveFieldPerm('document_assignments', MvvPerm::PERM_CREATE), 'range_type' => 'Studiengang', 'range_id' => $studiengang->id]) ?>
    </fieldset>

    <fieldset class="collapsable collapsed">
        <legend>
            <?= _('Ansprechpartner'); ?>
        </legend>
        <?= $this->render_partial('shared/contacts/range', ['perm_contacts' => $perm->haveFieldPerm('contact_assignments', MvvPerm::PERM_CREATE), 'range_type' => 'Studiengang', 'range_id' => $studiengang->id]) ?>
    </fieldset>

    <?= $plugin_hook_content ?>

    <footer>
        <? if ($studiengang->isNew()) : ?>
            <? if ($perm->havePermCreate()) : ?>
                <?= Button::createAccept(_('Anlegen'), 'store', ['title' => _('Studiengang anlegen')]) ?>
            <?= Button::createAccept(_('Anlegen und abbrechen'), 'store_cancel', ['title' => _('Studiengang anlegen und abbrechen')]) ?>
            <? endif; ?>
        <? else : ?>
            <? if ($perm->havePermWrite()) : ?>
            <?= Button::createAccept(_('Übernehmen'), 'store',
                    [
                        'title'      => _('Änderungen übernehmen'),
                        'formaction' => $controller->url_for('/studiengang', $studiengang->id),
                        'formmethod' => 'post'
                    ]) ?>
            <? endif; ?>
        <? endif; ?>
        <?= LinkButton::createCancel(_('Abbrechen'), $cancel_url, ['title' => _('zurück zur Übersicht')]) ?>
    </footer>
</form>

<script>
    $(document).ready(function() {
        $('#mvv-studycourse-types').select2({
            placeholder: '<?= _('Typ wählen') ?>'
        });
        STUDIP.MVV.Aufbaustg.loadTable('<?= $studiengang->id ?>');
    });
</script>
