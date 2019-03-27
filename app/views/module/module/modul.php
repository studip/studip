<? use Studip\Button, Studip\LinkButton; ?>
<?= $controller->jsUrl() ?>
<?
$perm = MvvPerm::get($modul);
$perm_d = MvvPerm::get($deskriptor);
if ($GLOBALS['MVV_MODUL']['SPRACHE']['default'] != $display_language) {
    $perm_d->setVariant($display_language);
}
?>
<? if (!$def_lang) : ?>
<script>
    MVV.PARENT_ID = '<?= $modul->getId() ?>';
</script>
<? endif; ?>

<form id="modul_form" class="default" action="<?= $controller->url_for('/modul', $modul->id) ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= _('Bezeichnung') ?></legend>
        <label id="mvv-field-modul-bezeichnung"><?= _('Modulbezeichnung') ?>
            <input <?= $perm_d->disable('bezeichnung') ?> type="text" name="bezeichnung" id="bezeichnung" value="<?= htmlReady($deskriptor->bezeichnung) ?>" required>
        </label>
        <label id="mvv-field-modul-code"><?= _('Modulcode') ?>
            <? if ($def_lang) : ?>
            <input <?= $perm->disable('code') ?>  type="text" name="code" id="code" value="<?= htmlReady($modul->code) ?>" maxlength="250">
            <? else : ?>
            <?= $modul->code ? htmlReady($modul->code) : _('keine Angabe') ?>
            <? endif; ?>
        </label>
    </fieldset>
    <fieldset>
        <legend><?= _('Relationen') ?></legend>
        <label id="mvv-field-modul-quelle"><?= _('Quelle') ?>
            <? if (!$modul->modul_quelle) : ?>
            <?= _('Dieses Modul hat keine Vorlage.') ?>
            <? else : ?>
            <?= sprintf(_('Dieses Modul ist eine Novellierung des Moduls <strong><em>%s</em></strong>.'), htmlReady($modul->modul_quelle->getDisplayName())) ?>
            <input type="hidden" name="quelle" id="quelle" value="<?= $modul->modul_quelle->id ?>">
            <? endif; ?>
        </label>
        <label id="mvv-field-modul-flexnow_modul"><?= _('Modul-ID Fremdsystem') ?>
            <? if ($def_lang) : ?>
            <input <?= $perm->disable('flexnow_modul') ?> type="text" name="flexnow_modul" id="flexnow_modul" value="<?= htmlReady($modul->flexnow_modul) ?>" maxlength="250">
            <? else : ?>
            <?= htmlReady($modul->flexnow_modul) ?>
            <? endif; ?>
        </label>
    </fieldset>
    <fieldset id="mvv-field-modul-variante">
        <legend><?= _('Ist Variante von') ?></legend>
        <? if ($def_lang) : ?>
        <? if ($perm->haveFieldPerm('modul_variante', MvvPerm::PERM_WRITE)) : ?>
        <div>
            <?= $search_modul->render(); ?>
            <? if (Request::submitted('search_modul')) : ?>
                <?= Icon::create('refresh', ['name' => 'reset_modul', 'data-qs_id' => $qs_id_module])->asInput(); ?>
            <? else : ?>
                <?= Icon::create('search', ['name' => 'search_modul', 'data-qs_id' => $qs_id_module, 'data-qs_name' => $search_modul->getId(), 'class' => 'mvv-qs-button'])->asInput(); ?>
            <? endif; ?>
        </div>
        <? endif; ?>
        <ul id="modul_target" class="mvv-assigned-items mvv-assign-single mvv-modul">
            <li class="mvv-item-list-placeholder"<?= ($modul->modul_variante ? ' style="display: none;"' : '') ?>><?= _('Dieses Modul ist nicht die Variante eines anderen Moduls.') ?></li>
            <? if ($modul->modul_variante->id) : ?>
            <li id="modul_<?= $modul->modul_variante->id ?>">
                <div class="mvv-item-list-text">
                    <?= htmlReady($modul->modul_variante->getDisplayName()) ?>
                </div>
                <? if ($perm->haveFieldPerm('modul_variante', MvvPerm::PERM_WRITE)) : ?>
                <div class="mvv-item-list-buttons">
                    <a href="#" class="mvv-item-remove"><?= Icon::create('trash', array('title' => _(' entfernen')))->asImg(); ?></a>
                </div>
                <? endif; ?>
                <input type="hidden" name="modul_item" value="<?= $modul->modul_variante->id ?>">
            </li>
            <? endif; ?>
        </ul>
        <? else : ?>
        <ul id="modul_target" class="mvv-assigned-items mvv-assign-single mvv-modul">
            <? if ($modul->modul_variante) : ?>
            <li id="modul_<?= $modul->modul_variante->id ?>">
                <div class="mvv-item-list-text">
                    <?= htmlReady($modul->modul_variante->getDisplayName()) ?>
                </div>
            </li>
            <? else : ?>
            <li class="mvv-item-list-placeholder"><?= _('Dieses Modul ist nicht die Variante eines anderen Moduls.') ?></li>
            <? endif; ?>
        </ul>
        <? endif; ?>
    </fieldset>
    <fieldset id="mvv-field-modul-gueltigkeit">
        <legend><?= _('Gültigkeit') ?></legend>
        <? if ($def_lang) : ?>
            <label id="mvv-field-modul-modul_start">
                <?= _('von Semester:') ?>
                <? if ($perm->haveFieldPerm('start')) : ?>
                <select name="start" size="1">
                    <option value=""><?= _('-- Semester wählen --') ?></option>
                <? foreach ($semester as $sem) : ?>
                    <option value="<?= $sem->semester_id ?>"<?= ($sem->semester_id == $modul->start ? ' selected' : '') ?>>
                        <?= htmlReady($sem->name) ?>
                    </option>
                <? endforeach; ?>
                </select>
                <? else : ?>
                    <? $sem = Semester::find($modul->start) ?>
                    <?= htmlReady($sem->name) ?>
                    <input type="hidden" name="start" value="<?= htmlReady($modul->start) ?>">
                <? endif; ?>
            </label>
            <label id="mvv-field-modul-modul_end">
                <?= _('bis Semester:') ?>
                <? if ($perm->haveFieldPerm('end')) : ?>
                <select name="end" size="1">
                    <option value=""><?= _('unbegrenzt gültig') ?></option>
                <? foreach ($semester as $sem) : ?>
                    <option value="<?= $sem->semester_id ?>"<?= ($sem->semester_id == $modul->end ? ' selected' : '') ?>>
                        <?= htmlReady($sem->name) ?>
                    </option>
                <? endforeach; ?>
                </select>
                <? else : ?>
                    <? if ($modul->end != '') : ?>
                        <? $sem = Semester::find($modul->end) ?>
                        <?= htmlReady($sem->name) ?>
                    <? else : ?>
                        <?= _('unbegrenzt gültig') ?>
                    <? endif; ?>
                    <input type="hidden" name="end" value="<?= htmlReady($modul->end) ?>">
                <? endif; ?>
            </label>
            <div><?= _('Das Endsemester wird nur angegeben, wenn das Modul abgeschlossen ist.') ?></div>
            <label id="mvv-field-modul-beschlussdatum">
                <?= _('Beschlussdatum:') ?>
                <? if ($perm->haveFieldPerm('beschlussdatum')) : ?>
                <input type="text" name="beschlussdatum" value="<?= ($modul->beschlussdatum ? strftime('%d.%m.%Y', $modul->beschlussdatum) : '') ?>" placeholder="<?= _('TT.MM.JJJJ') ?>" class="with-datepicker">
                <? else : ?>
                <?= ($modul->beschlussdatum ? strftime('%d.%m.%Y', $modul->beschlussdatum) : '') ?>
                <input type="hidden" name="beschlussdatum" value="<?= ($modul->beschlussdatum ? strftime('%d.%m.%Y', $modul->beschlussdatum) : '') ?>">
                <? endif; ?>
            </label>
            <label for="mvv-field-modul-fassung_nr"><?= _('Fassung:') ?>
                <section class="hgroup size-m">
                    <select<?= $perm->haveFieldPerm('fassung_nr') ? '' : ' disabled' ?> name="fassung_nr" id="mvv-field-modul-fassung_nr">
                        <option value="">--</option>
                    <? foreach (range(1, 30) as $nr) : ?>
                        <option<?= $nr == $modul->fassung_nr ? ' selected' : '' ?> value="<?= $nr ?>"><?= $nr ?>.</option>
                    <? endforeach; ?>
                    </select>
                    <? if (!$perm->haveFieldPerm('fassung_nr')) : ?>
                        <input type="hidden" name="fassung_nr" value="<?= htmlReady($modul->fassung_nr) ?>">
                    <? endif; ?>
                    <select<?= $perm->haveFieldPerm('fassung_typ') ? '' : ' disabled' ?> id="mvv-field-modul-fassung_typ" style="display: inline-block; max-width: 40em;" name="fassung_typ">
                        <option value="0">--</option>
                    <? foreach ($GLOBALS['MVV_MODUL']['FASSUNG_TYP'] as $key => $entry) : ?>
                        <option value="<?= $key ?>"<?= $key == $modul->fassung_typ ? ' selected' : '' ?>><?= htmlReady($entry['name']) ?></option>
                    <? endforeach; ?>
                    </select>
                    <? if (!$perm->haveFieldPerm('fassung_typ')) : ?>
                    <input type="hidden" name="fassung_typ" value="<?= htmlReady($modul->fassung_typ) ?>">
                    <? endif; ?>
                </section>
            </label>
            <label id="mvv-field-modul-version">
                <?= _('Version:') ?>
                <input <?= $perm->disable("version") ?> type="text" name="version" id="version" value="<?= htmlReady($modul->version) ?>" maxlength="120">
            </label>
        <? else: ?>
            <div id="mvv-field-modul-modul_start">
                <?
                $start_sem = Semester::find($modul->start);
                $end_sem = Semester::find($modul->end);
                printf(_('von Semester: %s bis Semester: %s'),
                        $start_sem ? htmlReady($start_sem->name) : _('unbekanntes Semester'),
                        $end_sem ? htmlReady($end_sem->name) : _('unbegrenzt gültig'));
                ?>
            </div>
            <div id="mvv-field-modul-beschlussdatum">
                <? printf(_('Beschlussdatum: %s'),
                        $modul->beschlussdatum ? strftime('%d.%m.%Y', $modul->beschlussdatum) : _('nicht angegeben')) ?>
            </div>
            <div id="mvv-field-modul-fassung_nr">
                <?
                if ($modul->fassung_nr) {
                    printf(
                        _('Fassung: %s. %s'),
                        htmlReady($modul->fassung_nr),
                        $GLOBALS['MVV_MODUL']['FASSUNG_TYP'][$modul->fassung_typ]['name']
                    );
                }
                ?>
            </div>
        <? endif; ?>
    </fieldset>
    <fieldset id="mvv-field-modul-responsible_institute">
        <legend><?= _('Verantwortliche Einrichtung') ?></legend>
        <? if ($def_lang && $perm->haveFieldPerm('responsible_institute', MvvPerm::PERM_WRITE)) : ?>
            <?= $search_responsible->render(); ?>
            <? if (Request::submitted('search_responsible')) : ?>
                <?= Icon::create('refresh', ['name' => 'reset_responsible', 'data-qs_id' => $qs_id_responsible])->asInput(); ?>
            <? else : ?>
                <?= Icon::create('search', 'clickable', ['name' => 'search_responsible', 'data-qs_id' => $qs_id_responsible, 'data-qs_name' => $search_responsible->getId(), 'class' => 'mvv-qs-button'])->asInput(); ?>
            <? endif; ?>
            <ul id="responsible_target" class="mvv-assigned-items mvv-assign-single mvv-institute">
                <? $display_institute = $modul->responsible_institute && $modul->responsible_institute->institut_id ?>
                <li class="mvv-item-list-placeholder"<?= ($display_institute ? ' style="display: none;"' : '') ?>><?= _('Bitte geben Sie eine verantwortliche Einrichtung an.') ?></li>
                <? if ($display_institute) : ?>
                    <li id="modul_<?= $modul->responsible_institute->institut_id; ?>">
                        <div class="mvv-item-list-text">
                            <? if ($modul->responsible_institute->institute) : ?>
                            <?= htmlReady($modul->responsible_institute->institute->getDisplayName()) ?>
                            <? else: ?>
                            <?= _('unbekannte Einrichtung') ?>
                            <? endif;?>
                        </div>
                        <div class="mvv-item-list-buttons">
                            <a href="#" class="mvv-item-remove"><?= Icon::create('trash', 'clickable', array('title' => _(' entfernen')))->asImg(); ?></a>
                        </div>
                        <input type="hidden" name="responsible_item" value="<?= $modul->responsible_institute->institut_id ?>">
                    </li>
                <? endif; ?>
            </ul>
        <? else : ?>
            <ul id="maininst_target" class="mvv-assigned-items mvv-assign-single mvv-institute">
            <? if ($modul->responsible_institute->institute) : ?>
                <li id="modul_<?= $modul->responsible_institute->institut_id ?>">
                    <div class="mvv-item-list-text">
                        <? if ($modul->responsible_institute->institute) : ?>
                        <?= htmlReady($modul->responsible_institute->institute->getDisplayName()) ?>
                        <? else : ?>
                        <?= _('unbekannte Einrichtung') ?>
                        <? endif;?>
                    </div>
                    <input type="hidden" name="responsible_item" value="<?= $modul->responsible_institute->institute->getId() ?>">
                </li>
            <? else : ?>
                <li class="mvv-item-list-placeholder"><?= _('Es wurde noch keine verantwortliche Einrichtung angegeben.') ?></li>
            <? endif; ?>
            </ul>
        <? endif; ?>
    </fieldset>
    <fieldset id="mvv-field-modul-assigned_institutes">
        <legend><?= _('Beteiligte Einrichtungen') ?></legend>
        <? if ($def_lang && $perm->haveFieldPerm('assigned_institutes', MvvPerm::PERM_WRITE)) : ?>
            <?= $search_institutes->render(); ?>
            <? if (Request::submitted('search_institutes')) : ?>
                <?= Icon::create('refresh', 'clickable', ['name' => 'reset_institutes', 'data-qs_id' => $qs_id_institutes])->asInput(); ?>
            <? else : ?>
                <?= Icon::create('search', 'clickable', ['name' => 'search_institutes', 'data-qs_id' => $qs_id_institutes, 'data-qs_name' => $search_institutes->getId(), 'class' => 'mvv-qs-button'])->asInput(); ?>
            <? endif; ?>
            <ul id="institutes_target" class="mvv-assigned-items sortable mvv-institute">
                <li class="mvv-item-list-placeholder"<?= (count($modul->assigned_institutes) ? ' style="display:none;"' : '') ?>>
                    <?= _('Geben Sie gegebenenfalls beteiligte Einrichtungen an.') ?>
                </li>
                <? foreach ($modul->assigned_institutes as $assigned_institute) : ?>
                <li id="institut_<?= $assigned_institute->institut_id ?>" class="sort_items">
                    <div class="mvv-item-list-text">
                        <? if ($assigned_institute->institute) : ?>
                        <?= htmlReady($assigned_institute->institute->Name) ?>
                        <? else : ?>
                        <?= _('unbekannte Einrichtung') ?>
                        <? endif; ?>
                    </div>
                    <div class="mvv-item-list-buttons">
                        <a href="#" class="mvv-item-remove">
                            <?= Icon::create('trash', 'clickable', array('title' => _('Einrichtung entfernen')))->asImg(); ?>
                        </a>
                    </div>
                    <input type="hidden" name="institutes_items[]" value="<?= $assigned_institute->institut_id ?>">
                </li>
                <? endforeach; ?>
            </ul>
            <?= _('Die Reihenfolge der beteiligten Einrichtungen kann durch Anklicken und Ziehen geändert werden.') ?>
        <? else : ?>
            <ul id="institute_target" class="mvv-assigned-items mvv-institute">
            <? if (sizeof($modul->assigned_institutes)) : ?>
                <? foreach ($modul->assigned_institutes as $assigned_institute) : ?>
                <li id="institut_<?= $assigned_institute->institut_id ?>">
                    <div class="mvv-item-list-text">
                        <? if ($assigned_institute->institute) : ?>
                        <?= htmlReady($assigned_institute->institute->Name) ?>
                        <? else : ?>
                        <?= _('unbekannte Einrichtung') ?>
                        <? endif; ?>
                    </div>
                    <input type="hidden" name="institute_items[]" value="<?= $assigned_institute->institut_id ?>">
                </li>
                <? endforeach; ?>
            <? else : ?>
                <li class="mvv-item-list-placeholder">
                    <?= _('Es wurden noch keine weiteren beteiligten Einrichtungen angegeben.') ?>
                </li>
            <? endif; ?>
            </ul>
        <? endif; ?>
    </fieldset>
    <fieldset id="mvv-field-modul-assigned_users">
        <legend><?= _('Personen') ?></legend>
        <div class="extendedLayout" id="<?= $qs_frame_id_users ?>_frame">
            <? if ($def_lang && $perm->haveFieldPerm('assigned_users', MvvPerm::PERM_WRITE)) : ?>
                <div class="mvv-select-group">
                    <label><?= _('Zuordnen mit der Funktion:') ?>
                        <select>
                            <? foreach ($GLOBALS['MVV_MODUL']['PERSONEN_GRUPPEN']['values'] as $gruppe_id => $gruppe) : ?>
                                <option value="<?= $gruppe_id ?>"><?= htmlReady($gruppe['name']) ?></option>
                            <? endforeach; ?>
                        </select>
                    </label>
                </div>
                <?= $search_users->render(); ?>
                <? if (Request::submitted('search_users')) : ?>
                    <?= Icon::create('refresh', 'clickable', ['name' => 'reset_users', 'data-qs_id' => $qs_id_users])->asInput(); ?>
                <? else : ?>
                    <?= Icon::create('search', 'clickable', ['name' => 'search_users', 'data-qs_id' => $qs_id_users, 'data-qs_name' => $search_users->getId(), 'class' => 'mvv-qs-button'])->asInput(); ?>
                <? endif; ?>
                <? $grouped_users = $modul->getGroupedAssignedUsers(); ?>
                <ul id="users_target" class="mvv-assigned-items sortable mvv-assign-group">
                    <li class="mvv-item-list-placeholder"<?= (count($modul->assigned_users) ? ' style="display:none;"' : '') ?>>
                        <?= _('Ordnen Sie dem Modul Personen mit ihren Funktionen zu.') ?>
                    </li>
                    <? foreach ($GLOBALS['MVV_MODUL']['PERSONEN_GRUPPEN']['values'] as $gruppe_id => $gruppe) : ?>
                    <li<?= is_array($grouped_users[$gruppe_id]) ? '' : ' style="display:none;"' ?>>
                        <?= $gruppe['name'] ?>
                        <ul id="users_<?= $gruppe_id ?>" class="sortable mvv-persons">
                        <? if (is_array($grouped_users[$gruppe_id])) : ?>
                            <? foreach ($grouped_users[$gruppe_id] as $assigned_user) : ?>
                                <li id="users_<?= $gruppe_id ?>_<?= $assigned_user->user_id ?>" class="sort_items">
                                    <div class="mvv-item-list-text"><?= htmlReady(get_fullname($assigned_user->user_id)) ?></div>
                                    <div class="mvv-item-list-buttons">
                                        <a href="#" class="mvv-item-remove">
                                            <?= Icon::create('trash', 'clickable', array('title' => _('Person entfernen')))->asImg(); ?>
                                        </a>
                                    </div>
                                    <input type="hidden" name="users_items_<?= $gruppe_id ?>[]" value="<?= $assigned_user->user_id ?>">
                                </li>
                            <? endforeach; ?>
                        <? endif; ?>
                        </ul>
                    </li>
                    <? endforeach; ?>
                </ul>
                <?= _('Die Reihenfolge der Personen kann innerhalb der Funktion durch Anklicken und Ziehen geändert werden.') ?>
            <? else : ?>
                <ul id="users_target" class="mvv-assigned-items mvv-assign-group">
                <? if (!count($modul->assigned_users)) : ?>
                    <li class="mvv-item-list-placeholder">
                        <?= _('Es wurden keine Personen mit ihren Funktionen zugeordnet.') ?>
                    </li>
                <? endif; ?>
                <? $grouped_users = $grouped_users = $modul->getGroupedAssignedUsers(); ?>
                <? foreach ($GLOBALS['MVV_MODUL']['PERSONEN_GRUPPEN']['values'] as $gruppe_id => $gruppe) : ?>
                    <? if (is_array($grouped_users[$gruppe_id])) : ?>
                    <li>
                        <?= $gruppe['name'] ?>
                        <ul id="users_<?= $gruppe_id ?>" class="mvv-persons">
                        <? if (is_array($grouped_users[$gruppe_id])) : ?>
                            <? foreach ($grouped_users[$gruppe_id] as $assigned_user) : ?>
                                <li id="person_<?= $assigned_user->user_id ?>">
                                    <div class="mvv-item-list-text"><?= htmlReady(get_fullname($assigned_user->user_id)) ?></div>
                                    <input type="hidden" name="users_items_<?= $gruppe_id ?>[]" value="<?= $assigned_user->user_id ?>">
                                </li>
                            <? endforeach; ?>
                        <? endif; ?>
                        </ul>
                    </li>
                    <? endif; ?>
                <? endforeach; ?>
                </ul>
            <? endif; ?>
        </div>
    </fieldset>
    <fieldset id="mvv-field-modul-verantwortlich">
        <legend><?= _('Weitere verantwortliche Personen') ?></legend>
        <label><?= _('Verantwortliche Personen') ?>
        <? if($perm_d->haveFieldPerm('verantwortlich', MvvPerm::PERM_WRITE)): ?>
           <textarea  name="verantwortlich" id="verantwortlich" cols="25" rows="6"><?= htmlReady($deskriptor->verantwortlich) ?></textarea>
        <? else: ?>
            <textarea readonly name="verantwortlich" id="verantwortlich" cols="25" rows="6"><?= htmlReady($deskriptor->verantwortlich) ?></textarea>
        <? endif; ?>
            <div>
            <?= _('Gegebenenfalls weitere Namen von verantwortlichen Personen, die keinen Account in Stud.IP haben. Oder allgemeine Angaben zur Verantwortlichkeit.') ?>
            </div>
    </fieldset>
    <fieldset id="mvv-field-modul-languages">
        <legend><?= _('Unterrichtssprachen') ?></legend>
        <? if ($def_lang && $perm->haveFieldPerm('languages', MvvPerm::PERM_WRITE)) : ?>
            <ul id="language_target" class="mvv-assigned-items sortable mvv-languages">
                <? if (!count($modul->languages)) : ?>
                <li class="mvv-item-list-placeholder"<?= (count($modul->languages) ? ' style="display:none;"' : '') ?>>
                    <?= _('Geben Sie die Unterrichtssprachen an.') ?>
                </li>
                <? endif; ?>
                <? foreach ($modul->languages as $assigned_language) : ?>
                <li id="language_<?= $assigned_language->lang ?>" class="sort_items">
                    <div class="mvv-item-list-text"><?= htmlReady($assigned_language->getDisplayName()) ?></div>
                    <div class="mvv-item-list-buttons">
                        <a href="#" class="mvv-item-remove"><?= Icon::create('trash', 'clickable', array('title' => _('Sprache entfernen')))->asImg(); ?></a>
                    </div>
                    <input type="hidden" name="language_items[]" value="<?= htmlReady($assigned_language->lang) ?>">
                </li>
                <? endforeach; ?>
            </ul>
            <?= $this->render_partial('shared/language_chooser', array('chooser_id' => 'language', 'chooser_languages' => $GLOBALS['MVV_MODUL']['SPRACHE']['values'])); ?>
            <?= _('Die Reihenfolge der Sprachen kann durch Anklicken und Ziehen geändert werden.') ?>
        <? else : ?>
            <ul id="languages_target" class="mvv-assigned-items mvv-languages">
            <? if (count($modul->languages)) : ?>
                <? foreach ($modul->languages as $assigned_language) : ?>
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
    </fieldset>
    <fieldset>
        <legend><?= _('Beschreibende Angaben') ?></legend>
        <label id="mvv-field-modul-voraussetzung"><?= _('Teilnahmevoraussetzung') ?>
        <? if($perm_d->haveFieldPerm('voraussetzung', MvvPerm::PERM_WRITE)): ?>
            <textarea cols="60" rows="5" name="voraussetzung" id="voraussetzung" class="add_toolbar ui-resizable wysiwyg"><?= htmlReady($deskriptor->voraussetzung) ?></textarea>
        <? else: ?>
            <textarea readonly cols="60" rows="5" name="voraussetzung" id="voraussetzung" class="ui-resizable"><?= htmlReady($deskriptor->voraussetzung) ?></textarea>
        <? endif; ?>
        </label>
        <label id="mvv-field-modul-kompetenzziele"><?= _('Kompetenzziele') ?>
       	<? if($perm_d->haveFieldPerm('kompetenzziele', MvvPerm::PERM_WRITE)): ?>
            <textarea cols="60" rows="5" name="kompetenzziele" id="kompetenzziele" class="add_toolbar ui-resizable wysiwyg"><?= htmlReady($deskriptor->kompetenzziele) ?></textarea>
       	<? else: ?>
            <textarea readonly cols="60" rows="5" name="kompetenzziele" id="kompetenzziele" class="ui-resizable"><?= htmlReady($deskriptor->kompetenzziele) ?></textarea>
        <? endif; ?>
        </label>
        <label id="mvv-field-modul-inhalte"><?= _('Inhalte') ?>
        <? if($perm_d->haveFieldPerm('inhalte', MvvPerm::PERM_WRITE)): ?>
            <textarea cols="60" rows="5" name="inhalte" id="inhalte" class="add_toolbar ui-resizable wysiwyg"><?= htmlReady($deskriptor->inhalte) ?></textarea>
        <? else: ?>
            <textarea readonly cols="60" rows="5" name="inhalte" id="inhalte" class="ui-resizable"><?= htmlReady($deskriptor->inhalte) ?></textarea>
        <? endif; ?>
        </label>
        <label id="mvv-field-modul-literatur"><?= _('Literatur') ?>
        <? if($perm_d->haveFieldPerm('literatur', MvvPerm::PERM_WRITE)): ?>
            <textarea cols="60" rows="5" name="literatur" id="literatur" class="add_toolbar ui-resizable wysiwyg"><?= htmlReady($deskriptor->literatur) ?></textarea>
        <? else: ?>
            <textarea readonly cols="60" rows="5" name="literatur" id="literatur" class="ui-resizable"><?= htmlReady($deskriptor->literatur) ?></textarea>
        <? endif; ?>
        </label>
        <label id="mvv-field-modul-links"><?= _('Links') ?>
        <? if($perm_d->haveFieldPerm('links', MvvPerm::PERM_WRITE)): ?>
            <textarea cols="60" rows="5" name="links" id="links" class="add_toolbar ui-resizable wysiwyg"><?= htmlReady($deskriptor->links) ?></textarea>
        <? else: ?>
            <textarea readonly cols="60" rows="5" name="links" id="links" class="ui-resizable"><?= htmlReady($deskriptor->links) ?></textarea>
        <? endif; ?>
        </label>
        <label id="mvv-field-modul-kommentar"><?= _('Kommentar') ?>
        <? if($perm_d->haveFieldPerm('kommentar', MvvPerm::PERM_WRITE)): ?>
            <textarea cols="60" rows="5" name="kommentar" id="kommentar" class="add_toolbar ui-resizable wysiwyg"><?= htmlReady($deskriptor->kommentar) ?></textarea>
        <? else: ?>
            <textarea readonly cols="60" rows="5" name="kommentar" id="kommentar" class="ui-resizable"><?= htmlReady($deskriptor->kommentar) ?></textarea>
        <? endif; ?>
        </label>
        <label id="mvv-field-modul-dauer"><?= _('Dauer (Semester)') ?>
        <? if ($def_lang) : ?>
            <input <?= $perm->disable('dauer') ?> type="text" name="dauer" id="dauer" value="<?= htmlReady($modul->dauer) ?>" maxlength="50">
        <? else : ?>
            <?= $modul->dauer ? htmlReady($modul->dauer) : _('keine Angabe') ?>
        <? endif; ?>
        </label>
        <label id="mvv-field-modul-turnus"><?= _('Turnus/Angebotsrhythmus') ?>
            <input <?= $perm_d->disable('turnus') ?> type="text" name="turnus" id="turnus" value="<?= htmlReady($deskriptor->turnus) ?>" maxlength="250">
        </label>
    </fieldset>
    <fieldset>
        <legend><?= _('Kapazität/Teilnahmezahl') ?></legend>
        <section  id="mvv-field-modul-kapazitaet" class="hgroup size-m">
        <? if ($perm->haveFieldPerm('kapazitaet') && $def_lang): ?>
            <label><?= _('Teilnahmezahl') ?>
                <input type="text" name="kapazitaet" id="kapazitaet" value="<?= htmlReady($modul->kapazitaet) ?>" <?= $modul->kapazitaet == '' ? ' disabled' : ''; ?>>
            </label>
            <label>
                <input type="checkbox" name="kap_unbegrenzt" id="kap_unbegrenzt" value="1"<?= $modul->kapazitaet == '' ? ' checked' : ''; ?> onchange="jQuery('#kapazitaet').attr('disabled', function(foo, attr){ jQuery(this).val(attr ? '0' : ''); return !attr; }); return false;">
                <?= _('unbegrenzt') ?>
            </label>
        <? else : ?>
            <?= _('Teilnahmezahl') ?>: <?= $modul->kapazitaet == '' ? _('unbegrenzt') : htmlReady($modul->kapazitaet) ?>
            <input type="hidden" name="kapazitaet" value="<?= htmlReady($modul->kapazitaet) ?>">
            <input type="hidden" name="kap_unbegrenzt" value="<?= $modul->kapazitaet == '' ? '1' : ''; ?>">
        <? endif; ?>
        </section>
        <label id="mvv-field-modul-kommentar_kapazitaet"><?= _('Kommentar') ?>
        <? if($perm_d->haveFieldPerm('kommentar_kapazitaet', MvvPerm::PERM_WRITE)): ?>
            <textarea cols="60" rows="5" name="kommentar_kapazitaet" id="kommentar_kapazitaet" class="add_toolbar ui-resizable wysiwyg"><?= htmlReady($deskriptor->kommentar_kapazitaet) ?></textarea>
	   <? else: ?>
            <textarea readonly cols="60" rows="5" name="kommentar_kapazitaet" id="kommentar_kapazitaet" class="ui-resizable"><?= htmlReady($deskriptor->kommentar_kapazitaet) ?></textarea>
	   <? endif; ?>
        </label>
    </fieldset>
    <fieldset>
        <legend><?= _('Kreditpunkte') ?></legend>
        <label id="mvv-field-modul-kp"><?= _('Kreditpunkte') ?>
        <? if ($def_lang) : ?>
            <input <?= $perm->disable('kp') ?> type="text" name="kp" id="kp" value="<?= htmlReady($modul->kp) ?>" maxlength="2">
        <? else : ?>
            <?= $modul->kp ? htmlReady($modul->kp) : _('keine Angabe') ?>
        <? endif; ?>
        </label>
    </fieldset>
    <fieldset id="mvv-field-modul-wl_selbst">
        <legend><?= _('Workload selbstgestaltete Arbeitszeit') ?></legend>
        <label><?= _('Stunden') ?>
        <? if ($def_lang) : ?>
            <input <?= $perm->disable('wl_selbst') ?> type="text" name="wl_selbst" id="wl_selbst" value="<?= htmlReady($modul->wl_selbst) ?>" maxlength="4">
        <? else : ?>
            <?= $modul->wl_selbst ? htmlReady($modul->wl_selbst) : _('keine Angabe') ?>
        <? endif; ?>
        </label>
        <label id="mvv-field-modul-kommentar_wl_selbst"><?= _('Kommentar') ?>
        <? if($perm_d->haveFieldPerm('kommentar_wl_selbst', MvvPerm::PERM_WRITE)): ?>
            <textarea cols="60" rows="5" name="kommentar_wl_selbst" id="kommentar_wl_selbst" class="add_toolbar ui-resizable wysiwyg"><?= htmlReady($deskriptor->kommentar_wl_selbst) ?></textarea>
        <? else: ?>
            <textarea readonly cols="60" rows="5" name="kommentar_wl_selbst" id="kommentar_wl_selbst" class="ui-resizable"><?= htmlReady($deskriptor->kommentar_wl_selbst) ?></textarea>
        <? endif; ?>
        </label>
    </fieldset>
    <fieldset id="mvv-field-modul-wl_pruef">
        <legend><?= _('Workload Prüfung') ?></legend>
        <label><?= _('Stunden') ?>
        <? if ($def_lang) : ?>
            <input <?= $perm->disable('wl_pruef') ?> type="text" name="wl_pruef" id="wl_pruef" value="<?= htmlReady($modul->wl_pruef) ?>" maxlength="4">
        <? else : ?>
            <?= $modul->wl_pruef ? htmlReady($modul->wl_pruef) : _('keine Angabe') ?>
        <? endif; ?>
        </label>
        <label id="mvv-field-modul-kommentar_wl_pruef"><?= _('Kommentar') ?>
        <? if($perm_d->haveFieldPerm('kommentar_wl_pruef', MvvPerm::PERM_WRITE)): ?>
            <textarea cols="60" rows="5" name="kommentar_wl_pruef" id="kommentar_wl_pruef" class="add_toolbar ui-resizable wysiwyg"><?= htmlReady($deskriptor->kommentar_wl_pruef) ?></textarea>
        <? else: ?>
            <textarea readonly cols="60" rows="5" name="kommentar_wl_pruef" id="kommentar_wl_pruef" class="ui-resizable"><?= htmlReady($deskriptor->kommentar_wl_pruef) ?></textarea>
        <? endif; ?>
        </label>
    </fieldset>
    <fieldset id="mvv-field-modul-kommentar_note">
        <legend><?= _('Kommentar Note') ?></legend>
        <label><?= _('Kommentar') ?>
        <? if($perm_d->haveFieldPerm('kommentar_note', MvvPerm::PERM_WRITE)): ?>
            <textarea cols="60" rows="5" name="kommentar_note" id="kommentar_note" class="add_toolbar ui-resizable wysiwyg"><?= htmlReady($deskriptor->kommentar_note) ?></textarea>
        <? else: ?>
            <textarea readonly cols="60" rows="5" name="kommentar_note" id="kommentar_note" class="ui-resizable"><?= htmlReady($deskriptor->kommentar_note) ?></textarea>
        <? endif; ?>
        </label>
    </fieldset>
    <fieldset id="mvv-field-modul-pruef_ebene">
        <legend><?= _('Prüfungsebene') ?></legend>
        <? if ($def_lang && $perm->haveFieldPerm('pruef_ebene', MvvPerm::PERM_WRITE)) : ?>
            <? foreach ($GLOBALS['MVV_MODUL']['PRUEF_EBENE']['values'] as $key => $ebene) : ?>
            <label>
                <input type="radio" name="pruef_ebene" value="<?= $key ?>"<?= $modul->pruef_ebene == $key ? ' checked' : '' ?>>
                <?= $ebene['name'] ?>
            </label>
            <? endforeach; ?>
        <? else : ?>
            <?= $GLOBALS['MVV_MODUL']['PRUEF_EBENE']['values'][$modul->pruef_ebene]['name'] ?>
            <input type="hidden" name="pruef_ebene" value="<?= $modul->pruef_ebene ?>">
        <? endif; ?>
    </fieldset>
    <fieldset>
        <legend><?= _('Prüfung') ?></legend>
        <label id="mvv-field-modul-pruef_vorleistung"><?= _('Prüfungsvorleistung') ?>
        <? if ($perm_d->haveFieldPerm('pruef_vorleistung', MvvPerm::PERM_WRITE)) : ?>
            <textarea cols="60" rows="5" name="pruef_vorleistung" id="pruef_vorleistung" class="add_toolbar ui-resizable wysiwyg"><?= htmlReady($deskriptor->pruef_vorleistung) ?></textarea>
        <? else : ?>
            <textarea readonly cols="60" rows="5" name="pruef_vorleistung" id="pruef_vorleistung" class="ui-resizable"><?= htmlReady($deskriptor->pruef_vorleistung) ?></textarea>
        <? endif; ?>
        </label>
        <label id="mvv-field-modul-pruef_leistung"><?= _('Leistung/Prüfungsform') ?>
        <? if ($perm_d->haveFieldPerm('pruef_leistung', MvvPerm::PERM_WRITE)) : ?>
            <textarea cols="60" rows="5" name="pruef_leistung" id="pruef_leistung" class="add_toolbar ui-resizable wysiwyg"><?= htmlReady($deskriptor->pruef_leistung) ?></textarea>
        <? else : ?>
            <textarea readonly cols="60" rows="5" name="pruef_leistung" id="pruef_leistung" class="ui-resizable"><?= htmlReady($deskriptor->pruef_leistung) ?></textarea>
        <? endif; ?>
        </label>
        <label id="mvv-field-modul-pruef_wiederholung"><?= _('Wiederholungsprüfung') ?>
        <? if ($perm_d->haveFieldPerm('pruef_wiederholung', MvvPerm::PERM_WRITE)) : ?>
            <textarea cols="60" rows="5" name="pruef_wiederholung" id="pruef_wiederholung" class="add_toolbar ui-resizable wysiwyg"><?= htmlReady($deskriptor->pruef_wiederholung) ?></textarea>
        <? else : ?>
            <textarea readonly cols="60" rows="5" name="pruef_wiederholung" id="pruef_wiederholung" class="ui-resizable"><?= htmlReady($deskriptor->pruef_wiederholung) ?></textarea>
        <? endif; ?>
        </label>
    </fieldset>
    <fieldset>
        <legend><?= _('Weitere Angaben') ?></legend>
        <label id="mvv-field-modul-faktor_note"><?= _('Faktor der Modulnote für die Endnote des Studiengangs') ?>
        <? if ($def_lang) : ?>
            <input <?= $perm->disable('faktor_note') ?> type="text" name="faktor_note" id="faktor_note" value="<?= htmlReady($modul->faktor_note) ?>" maxlength="4">
        <? else : ?>
            <?= $modul->faktor_note ? htmlReady($modul->faktor_note) : _('keine Angabe') ?>
        <? endif; ?>
        </label>
        <label id="mvv-field-modul-ersatztext"><?= _('Ersatztext') ?>
        <? if ($perm_d->haveFieldPerm('ersatztext', MvvPerm::PERM_WRITE)) : ?>
            <textarea cols="60" rows="5" name="ersatztext" id="ersatztext" class="add_toolbar ui-resizable wysiwyg"><?= htmlReady($deskriptor->ersatztext) ?></textarea>
        <? else : ?>
            <textarea readonly cols="60" rows="5" name="ersatztext" id="ersatztext" class="ui-resizable"><?= htmlReady($deskriptor->ersatztext) ?></textarea>
        <? endif; ?>
        </label>
        <? foreach ($deskriptor->datafields as $entry) : ?>
            <? if ($entry->lang == '') : ?>
                <? if (!$def_lang) : ?>
                    <? $df = new DatafieldEntryModel(
                            [
                                $entry->datafield_id,
                                $entry->range_id,
                                $entry->sec_range_id,
                                $language == 'de_DE' ? '' : $language
                            ]); ?>
                <? else :?>
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
    <fieldset id="mvv-field-modul-status">
        <legend><?= _('Bearbeitungsstatus und Sichtbarkeit') ?></legend>
        <?= _('Status') ?>
        <? $modul_stat = $modul->isNew() ? $GLOBALS['MVV_MODUL']['STATUS']['default'] : $modul->stat; ?>
        <? if ($def_lang) : ?>
            <input type="hidden" name="status" value="<?= $modul_stat ?>">
            <? foreach ($GLOBALS['MVV_MODUL']['STATUS']['values'] as $key => $status_modul) : ?>
            <? // The MVVAdmin have always PERM_CREATE for all fields ?>
            <? if ($perm->haveFieldPerm('stat', MvvPerm::PERM_CREATE) && $modul_stat != 'planung') : ?>
            <label>
                <input type="radio" name="status" value="<?= $key ?>"<?= ($modul_stat == $key ? ' checked' : '') ?>>
                <?= $status_modul['name'] ?>
            </label>
            <? elseif ($perm->haveFieldPerm('stat', MvvPerm::PERM_WRITE) && $modul_stat != 'planung') : ?>
            <label>
                <input <?= ($modul_stat == 'ausgelaufen' && $key == 'genehmigt')  ? 'disabled' :'' ?> type="radio" name="status" value="<?= $key ?>"<?= ($modul_stat == $key ? ' checked' : '') ?>>
                <?= $status_modul['name'] ?>
            </label>
            <? elseif($modul_stat == $key) : ?>
            	<?= $status_modul['name'] ?>
            <? endif; ?>
            <? endforeach; ?>
            <label id="mvv-field-modul-kommentar_status"><?= _('Kommentar:') ?>
                <? if ($perm->haveFieldPerm('kommentar_status', MvvPerm::PERM_WRITE)): ?>
                <textarea cols="60" rows="5" name="kommentar_status" id="kommentar_status" class="add_toolbar ui-resizable wysiwyg"><?= htmlReady($modul->kommentar_status) ?></textarea>
                <? else : ?>
                <textarea readonly cols="60" rows="5" name="kommentar_status" id="kommentar_status" class="ui-resizable"><?= htmlReady($modul->kommentar_status) ?></textarea>
                <? endif; ?>
            </label>
        <? else : ?>
            <?= $GLOBALS['MVV_MODUL']['STATUS']['values'][$modul->stat]['name'] ?>
            <div id="mvv-field-modul-kommentar_status" style="padding-top:10px;">
                <div><?= _('Kommentar') ?></div>
                <?= htmlReady($modul->kommentar_status) ?>
            </div>
        <? endif; ?>
    </fieldset>
    <input type="hidden" name="display_language" value="<?= $display_language ?>">
    <footer>
    <? if ($deskriptor->isNew()) : ?>
        <? if ($perm_d->havePermCreate()) : ?>
            <?= Button::createAccept(_('Anlegen'), 'store', array('title' => _('Modul anlegen'))) ?>
        <? endif; ?>
    <? else : ?>
        <? if ($perm_d->havePermWrite()) : ?>
            <?= Button::createAccept(_('Übernehmen'), 'store', array('title' => _('Änderungen übernehmen'))) ?>
        <? endif; ?>
        <? if (!$def_lang && !$deskriptor->isNew() && in_array($display_language, $translations)) : ?>
            <?= Button::create(_('Löschen'), 'delete', ['title' => _('Deskriptor löschen'), 'data-confirm' => sprintf(_('Soll der Deskriptor in der Ausgabesprache %s gelöscht werden?'), $GLOBALS['MVV_LANGUAGES']['values'][$display_language]['name']),
                'formaction' => $controller->url_for('/delete_modul_deskriptor', $deskriptor->id, $display_language)]); ?>
        <? endif; ?>
    <? endif; ?>
        <?= LinkButton::createCancel(_('Abbrechen'), $cancel_url, array('title' => _('zurück zur Übersicht'))) ?>
    </footer>
</form>
<? if (!$def_lang) : ?>
<script>
    jQuery('#modul_form').find('textarea, input[type=text]').after('<div style="padding-top:10px;"><a href="#" title="<?= _('Originalfassung anzeigen') ?>" class="mvv-show-original" data-type="modul"><img src="<?= Assets::image_path('languages/lang_' . mb_strtolower($modul->getDefaultLanguage()) . '.gif') ?>" alt="<?= _('Originalfassung') ?>"></a></div>');
</script>
<? endif; ?>
