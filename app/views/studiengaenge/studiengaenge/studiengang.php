<? use Studip\Button, Studip\LinkButton; ?>
<?= $controller->jsUrl() ?>
<? $perm = MvvPerm::get($studiengang) ?>
<form class="default" action="<?= $controller->url_for('/studiengang', $studiengang->id) ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <? if ($perm->haveFieldPerm('name', MvvPerm::PERM_WRITE)) : ?>
        <fieldset>
            <legend><?= _('Übernahme aus Fach') ?></legend>
            <?= _('Soll der Name des Studiengangs mit dem eines Fachs übereinstimmen, geben Sie den Namen des Fachs ein, und wählen Sie das Fach aus der Liste. Es werden dann automatisch die weiteren Bezeichnungen aus den Daten des Fachs übernommen.') ?>
            <label>
                <?= _('Fachsuche:') ?>
                <?= $search ?>
            </label>
        </fieldset>
    <? endif; ?>
    <fieldset>
        <legend><?= _('Bezeichnung') ?></legend>
        <label>
            <?= _('Name:') ?>
            <?= MvvI18N::input('name', $studiengang->name, ['maxlength' => '255'])->checkPermission($studiengang) ?>
        </label>
        <label><?= _('Kurzbezeichnung:') ?>
            <?= MvvI18N::input('name_kurz', $studiengang->name_kurz, ['maxlength' => '50'])->checkPermission($studiengang) ?>
        </label>
    </fieldset>
    <fieldset>
        <legend><?= _('Gültigkeit') ?></legend>
        <label>
            <?= _('von Semester:') ?>
            <? if ($perm->haveFieldPerm('start')) : ?>
                <select name="start" size="1">
                    <option value=""><?= _('-- Semester wählen --') ?></option>
                    <? foreach ($semester as $sem) : ?>
                        <option value="<?= $sem->semester_id ?>"<?= ($sem->semester_id == $studiengang->start ? ' selected' : '') ?>>
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
                        <option value="<?= $sem->semester_id ?>"<?= ($sem->semester_id == $studiengang->end ? ' selected' : '') ?>>
                            <?= htmlReady($sem->name) ?>
                        </option>
                    <? endforeach; ?>
                </select>
            <? else : ?>
                <? if ($studiengang->end != '') : ?>
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
        <label><?= _('Fassung:') ?></label>
        <select<?= $perm->disable('fassung_nr') ?> name="fassung_nr" style="display: inline-block; width: 5em;">
            <option value="">--</option>
            <? foreach (range(1, 30) as $nr) : ?>
                <option<?= $nr == $studiengang->fassung_nr ? ' selected' : '' ?> value="<?= $nr ?>"><?= $nr ?>.</option>
            <? endforeach; ?>
        </select>
        <? if ($perm->haveFieldPerm('fassung_typ')): ?>
            <select style="display: inline-block; max-width: 40em;" name="fassung_typ">
                <option value="0">--</option>
                <? foreach ($GLOBALS['MVV_STUDIENGANG']['FASSUNG_TYP'] as $key => $entry) : ?>
                    <option value="<?= $key ?>"<?= $key == $studiengang->fassung_typ ? ' selected' : '' ?>><?= htmlReady($entry['name']) ?></option>
                <? endforeach; ?>
            </select>
        <? else: ?>
            <?= ($studiengang->fassung_typ == '0' ? '--' : $GLOBALS['MVV_STUDIENGANG']['FASSUNG_TYP'][$studiengang->fassung_typ]['name']) ?>
            <input type="hidden" name="fassung_typ" value="<?= $studiengang->fassung_typ ?>">
        <? endif; ?>
    </fieldset>
    <fieldset>
        <legend><?= _('Beschreibung') ?></legend>
        <label>
            <?= MvvI18N::textarea('beschreibung', $studiengang->beschreibung, ['class' => 'add_toolbar ui-resizable wysiwyg'])->checkPermission($studiengang) ?>
        </label>
    </fieldset>
    <fieldset>
        <legend><?= _('Studiengangteile') ?></legend>
        <? if ($perm->haveFieldPerm('typ', MvvPerm::PERM_WRITE)) : ?>
            <label><input id="stg_typ" class="mvv_toggle_hide" type="radio" name="stg_typ"<?=
                ($studiengang->typ != 'mehrfach' ? ' checked' : '') ?>
                          value="einfach"> <?= _('Einfach-Studiengang (Diesem Studiengang wird ein oder mehrere Studiengangteil(e) direkt zugewiesen)') ?>
            </label>
        <? else : ?>
            <input type="hidden" name="stg_typ" value="<?= htmlReady($studiengang->typ) ?>">
        <? endif; ?>
        <? if ($perm->haveFieldPerm('typ', MvvPerm::PERM_WRITE)) : ?>
            <label><input class="mvv_toggle_hide" type="radio" name="stg_typ"<?=
                ($studiengang->typ == 'mehrfach' ? ' checked' : '') ?>
                          value="mehrfach"> <?= _('Mehrfach-Studiengang (Diesem Studiengang können mehrere Studiengangteile in Abschnitten zugewiesen werden)') ?>
            </label>
        <? endif; ?>
    </fieldset>
    <fieldset>
        <legend><?= _('Abschluss') ?></legend>
        <? if ($perm->haveFieldPerm('abschluss_id')) : ?>
            <select id="abschluss_id" name="abschluss_id" size="1">
                <option value=""><?= _('-- bitte wählen --') ?></option>
                <? foreach ($abschluesse as $abschluss) : ?>
                    <option
                        <?= ($abschluss['abschluss_id'] == $studiengang->abschluss_id ? 'selected ' : '') ?>value="<?= $abschluss['abschluss_id'] ?>"><?= htmlReady($abschluss['name']) ?></option>
                <? endforeach; ?>
            </select>
        <? else: ?>
            <? $abschluss = Abschluss::find($studiengang->abschluss_id) ?>
            <?= htmlReady($abschluss['name']) ?>
            <input type="hidden" name="abschluss_id" value="<?= $studiengang->abschluss_id ?>">
        <? endif; ?>
    </fieldset>
    <fieldset>
        <legend><?= _('Verantwortliche Einrichtung') ?></legend>
        <? if ($perm->haveFieldPerm('institut_id', MvvPerm::PERM_WRITE)): ?>
            <?= $search_institutes->render(); ?>
            <? if (Request::submitted('search_institutes')) : ?>
                <?= Icon::create('refresh', 'clickable', ['name' => 'reset_institutes', 'data-qs_id' => $search_institutes_id])->asInput(); ?>
            <? else : ?>
                <?= Icon::create('search', 'clickable', ['name' => 'search_institutes', 'data-qs_id' => $search_institutes_id, 'data-qs_name' => $search_institutes->getId(), 'class' => 'mvv-qs-button'])->asInput(); ?>
            <? endif; ?>
        <? endif; ?>
        <ul id="institut_target" class="mvv-assigned-items mvv-assign-single mvv-institute">
            <li class="mvv-item-list-placeholder"<?= ($studiengang->institut_id ? ' style="display: none;"' : '') ?>><?= _('Bitte eine Einrichtung suchen und zuordnen.') ?></li>
            <? if ($studiengang->institut_id) : ?>
                <li id="institut_<?= $studiengang->institut_id ?>">
                    <div class="mvv-item-list-text">
                        <? if ($institut) : ?>
                            <?= htmlReady($institut->getDisplayName()) ?>
                        <? else: ?>
                            <?= _('unbekannte Einrichtung') ?>
                        <? endif; ?>
                    </div>
                    <? if ($perm->haveFieldPerm('institut_id', MvvPerm::PERM_WRITE)): ?>
                        <div class="mvv-item-list-buttons">
                            <a href="#"
                               class="mvv-item-remove"><?= Icon::create('trash', 'clickable', ['title' => _('Einrichtung entfernen')])->asImg(); ?></a>
                        </div>
                    <? endif; ?>
                    <input type="hidden" name="institut_item" value="<?= $studiengang->institut_id ?>">
                </li>
            <? endif; ?>
        </ul>
    </fieldset>
    <?= $this->render_partial('shared/form_dokumente', ['perm_dokumente' => $perm->haveFieldPerm('document_assignments', MvvPerm::PERM_CREATE)]) ?>
    <fieldset>
        <legend><?= _('Status der Bearbeitung') ?></legend>
        <? if ($perm->haveFieldPerm('stat', MvvPerm::PERM_WRITE) && $studiengang->stat != 'planung'): ?>
            <? foreach ($GLOBALS['MVV_STUDIENGANG']['STATUS']['values'] as $key => $status_bearbeitung) : ?>
                <label>
                    <input <?= ($studiengang->stat == 'ausgelaufen' && $key == 'genehmigt') ? 'disabled' : '' ?>
                            type="radio" name="status"
                            value="<?= $key ?>"<?= $studiengang->stat == $key ? ' checked' : '' ?>>
                    <?= $status_bearbeitung['name'] ?>
                </label>
            <? endforeach; ?>
        <? else : ?>
            <div>
                <?= $GLOBALS['MVV_STUDIENGANG']['STATUS']['values'][$studiengang->stat]['name'] ?>
                <input type="hidden" name="status" value="<?= $studiengang->stat ?>">
            </div>
        <? endif; ?>
        <br>
        <label for="kommentar_status" style="vertical-align: top;"><?= _('Kommentar:') ?></label>
        <? if ($perm->haveFieldPerm('kommentar_status', MvvPerm::PERM_WRITE)): ?>
            <textarea cols="60" rows="5" name="kommentar_status" id="kommentar_status"
                      class="add_toolbar ui-resizable wysiwyg"><?= htmlReady($studiengang->kommentar_status) ?></textarea>
        <? else: ?>
            <textarea readonly cols="60" rows="5" name="kommentar_status" id="kommentar_status"
                      class="ui-resizable"><?= htmlReady($studiengang->kommentar_status) ?></textarea>
        <? endif; ?>
    </fieldset>
    <fieldset>
        <legend><?= _('Schlagworte') ?></legend>
        <textarea <?= $perm->disable('schlagworte') ?> cols="60" rows="5" name="schlagworte" id="schlagworte"
                                                       class="ui-resizable"><?= htmlReady($studiengang->schlagworte) ?></textarea>
        <div><?= _('Hier können zusätzlich Schlagworte angegeben werden, die in der Suche berücksichtigt werden.') ?></div>
    </fieldset>
    <footer>
        <? if ($studiengang->isNew()) : ?>
            <? if ($perm->havePermCreate()) : ?>
                <?= Button::createAccept(_('Anlegen'), 'store', ['title' => _('Studiengang anlegen')]) ?>
            <? endif; ?>
        <? else : ?>
            <? if ($perm->havePermWrite()) : ?>
                <?= Button::createAccept(_('Übernehmen'), 'store', ['title' => _('Änderungen übernehmen')]) ?>
            <? endif; ?>
        <? endif; ?>
        <?= LinkButton::createCancel(_('Abbrechen'), $cancel_url, ['title' => _('zurück zur Übersicht')]) ?>
    </footer>
</form>
