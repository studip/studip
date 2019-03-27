<? use Studip\Button, Studip\LinkButton; ?>
<?= $controller->jsUrl() ?>
<? $perm = MvvPerm::get($version) ?>

<form class="default" action="<?= $controller->url_for('/version', $stgteil->id, $version->id) ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= _('Gültigkeit') ?></legend>
        <label>
            <?= _('von Semester:') ?>
            <? if ($perm->haveFieldPerm('start_sem')) : ?>
            <select name="start_sem" size="1">
                <option value=""><?= _('-- Semester wählen --') ?></option>
            <? foreach ($semester as $sem) : ?>
                <option value="<?= $sem->semester_id ?>"<?= ($sem->semester_id == $version->start_sem ? ' selected' : '') ?>>
                    <?= htmlReady($sem->name) ?>
                </option>
            <? endforeach; ?>
            </select>
            <? else : ?>
                <? $sem = Semester::find($version->start_sem) ?>
                <?= htmlReady($sem->name) ?>
                <input type="hidden" name="start_sem" value="<?= $version->start_sem ?>">
            <? endif; ?>
        </label>
        <label>
            <?= _('bis Semester:') ?>
            <? if ($perm->haveFieldPerm('end_sem')) : ?>
            <select name="end_sem" size="1">
                <option value=""><?= _('unbegrenzt gültig') ?></option>
            <? foreach ($semester as $sem) : ?>
                <option value="<?= $sem->semester_id ?>"<?= ($sem->semester_id == $version->end_sem ? ' selected' : '') ?>>
                    <?= htmlReady($sem->name) ?>
                </option>
            <? endforeach; ?>
            </select>
            <? else : ?>
                <? if ($version->end_sem != "") : ?>
                    <? $sem = Semester::find($version->end_sem) ?>
                    <?= htmlReady($sem->name) ?>
                <? else : ?>
                    <?= _('unbegrenzt gültig') ?>
                <? endif; ?>
                <input type="hidden" name="end_sem" value="<?= $version->end_sem ?>">
            <? endif; ?>
        </label>
        <div><?= _('Das Endsemester wird nur angegeben, wenn die Version abgeschlossen ist.') ?></div>
        <label>
            <?= _('Beschlussdatum:') ?>
            <? if ($perm->haveFieldPerm('beschlussdatum')) : ?>
            <input type="text" name="beschlussdatum" value="<?= ($version->beschlussdatum ? strftime('%d.%m.%Y', $version->beschlussdatum) : '') ?>" placeholder="<?= _('TT.MM.JJJJ') ?>" class="with-datepicker">
            <? else : ?>
            <?= ($version->beschlussdatum ? strftime('%d.%m.%Y', $version->beschlussdatum) : '') ?>
            <input type="hidden" name="beschlussdatum" value="<?= ($version->beschlussdatum ? strftime('%d.%m.%Y', $version->beschlussdatum) : '') ?>">
            <? endif; ?>
        </label>
        <label for="fassung_nr"><?= _('Fassung:') ?></label>
        <section class="hgroup">
            <select<?= $perm->haveFieldPerm('fassung_nr') ? '' : ' disabled' ?> name="fassung_nr" id="fassung_nr" class="size-s">
                <option value="">--</option>
            <? foreach (range(1, 30) as $nr) : ?>
                <option<?= $nr == $version->fassung_nr ? ' selected' : '' ?> value="<?= $nr ?>"><?= $nr ?>.</option>
            <? endforeach; ?>
            </select>
            <? if (!$perm->haveFieldPerm('fassung_nr')) : ?>
            <input type="hidden" name="fassung_nr" value="<?= htmlReady($version->fassung_nr) ?>">
            <? endif; ?>
            <select<?= $perm->haveFieldPerm('fassung_typ') ? '' : ' disabled' ?> name="fassung_typ">
                <option value="0">--</option>
            <? foreach ($GLOBALS['MVV_STGTEILVERSION']['FASSUNG_TYP'] as $key => $entry) : ?>
                <option value="<?= $key ?>"<?= $key == $version->fassung_typ ? ' selected' : '' ?>><?= htmlReady($entry['name']) ?></option>
            <? endforeach; ?>
            </select>
            <? if (!$perm->haveFieldPerm('fassung_typ')) : ?>
            <input type="hidden" name="fassung_typ" value="<?= $version->fassung_typ ?>">
            <? endif; ?>
        </section>
    </fieldset>
    <fieldset>
        <legend><?= _('Code') ?></legend>
            <input <?= $perm->disable('code') ?> type="text" name="code" id="code" value="<?= htmlReady($version->code) ?>" maxlength="100">
    </fieldset>
    <fieldset>
        <legend><?= _('Beschreibung') ?></legend>
        <?= MvvI18N::textarea('beschreibung', $version->beschreibung, ['class' => 'add_toolbar ui-resizable wysiwyg', 'id' => 'beschreibung'])->checkPermission($version) ?>
    </fieldset>
    <? $url = $controller->url_for('/dokumente_properties'); ?>
    <? $perm_dokumente = $perm->haveFieldPerm('document_assignments', MvvPerm::PERM_CREATE) ?>
    <?= $this->render_partial('shared/form_dokumente', compact('search_dokumente', 'dokumente', 'url', 'perm_dokumente')) ?>
    <fieldset>
        <legend><?= _('Status der Bearbeitung') ?></legend>
        <input type="hidden" name="status" value="<?= $version->stat ?>">
        <? foreach ($GLOBALS['MVV_STGTEILVERSION']['STATUS']['values'] as $key => $status_bearbeitung) : ?>
        <? // The MVVAdmin have always PERM_CREATE for all fields ?>
        <? if ($perm->haveFieldPerm('stat', MvvPerm::PERM_CREATE) && $version->stat != 'planung') : ?>
        <label>
            <input type="radio" name="status" value="<?= $key ?>"<?= ($version->stat == $key ? ' checked' : '') ?>>
            <?= $status_bearbeitung['name'] ?>
        </label>
        <? elseif ($perm->haveFieldPerm('stat', MvvPerm::PERM_WRITE) && $version->stat != 'planung') : ?>
        <label>
            <input <?= ($version->stat == 'ausgelaufen' && $key == 'genehmigt')  ? 'disabled' :'' ?> type="radio" name="status" value="<?= $key ?>"<?= ($version->stat == $key ? ' checked' : '') ?>>
            <?= $status_bearbeitung['name'] ?>
        </label>
        <? elseif($version->stat == $key) : ?>
            <?= $status_bearbeitung['name'] ?>
        <? endif; ?>
        <? endforeach; ?>
        <label for="kommentar_status" style="vertical-align: top;"><?= _('Kommentar:') ?></label>
        <? if($perm->haveFieldPerm('kommentar_status', MvvPerm::PERM_WRITE)) : ?>
        <textarea cols="60" rows="5" name="kommentar_status" id="kommentar_status" class="add_toolbar ui-resizable wysiwyg"><?= htmlReady($version->kommentar_status) ?></textarea>
        <? else : ?>
        <textarea disabled cols="60" rows="5" name="kommentar_status" id="kommentar_status" class="ui-resizable"><?= htmlReady($version->kommentar_status) ?></textarea>
        <? endif; ?>
    </fieldset>
    <footer data-dialog-button>
        <? if ($version->isNew()) : ?>
            <? if ($perm->havePermCreate()) : ?>
            <?= Button::createAccept(_('Anlegen'), 'store', ['title' => _('Version anlegen')]) ?>
            <? endif; ?>
        <? else : ?>
            <? if ($perm->havePermWrite()) : ?>
            <?= Button::createAccept(_('Übernehmen'), 'store', ['title' => _('Änderungen übernehmen')]) ?>
            <? endif; ?>
        <? endif; ?>
        <?= LinkButton::createCancel(_('Abbrechen'), $cancel_url, ['title' => _('zurück zur Übersicht')]) ?>
    </footer>
</form>
