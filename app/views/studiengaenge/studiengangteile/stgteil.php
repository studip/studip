<? use Studip\Button, Studip\LinkButton; ?>
<?= $controller->jsUrl() ?>
<? $perm = MvvPerm::get($stgteil) ?>

<form class="default" action="<?= $controller->url_for('/stgteil/' . $stgteil->id) ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset class="collapsable">
        <legend>
            <?= _('Fach') ?>
        </legend>
        <? if (is_array($faecher)) : ?>
            <label>
                <?= sprintf(_('Mögliche Fächer im gewählten Fachbereich %s:'), '<strong>' . htmlReady($fachbereich->name) . '</strong>') ?>
                <select name="fach_item">
                    <option value="">-- <?= _('Bitte wählen') ?> --</option>
                    <? foreach ($faecher as $fach) : ?>
                        <option value="<?= $fach->id ?>"><?= htmlReady($fach->name) ?></option>
                    <? endforeach; ?>
                </select>
            </label>
        <? else : ?>
            <? if ($perm->haveFieldPerm('fach', MvvPerm::PERM_WRITE)) : ?>
                <?= $search_fach->render() ?>
                <? if (Request::submitted('search_fach')) : ?>
                    <?= Icon::create('refresh', Icon::ROLE_CLICKABLE, ['name' => 'reset_fach', 'data-qs_id' => $search_fach_id])->asInput(); ?>
                <? else : ?>
                    <?= Icon::create('search', Icon::ROLE_CLICKABLE, ['name' => 'search_fach', 'data-qs_id' => $search_fach_id, 'data-qs_name' => $search_fach->getId(), 'class' => 'mvv-qs-button'])->asInput(); ?>
                <? endif; ?>
            <? endif; ?>
            <ul id="fach_target" class="mvv-assigned-items mvv-assign-single mvv-faecher">
                <li class="mvv-item-list-placeholder"<?= ($stgteil->fach ? ' style="display: none;"' : '') ?>>
                    <?= _('Bitte ein Fach suchen und zuordnen.') ?>
                </li>
                <? if ($stgteil->fach) : ?>
                    <li id="fach_<?= $stgteil->fach->id ?>">
                        <div class="mvv-item-list-text">
                            <?= htmlReady($stgteil->fach->name) ?>
                        </div>
                        <? if ($perm->haveFieldPerm('fach', MvvPerm::PERM_WRITE)) : ?>
                            <div class="mvv-item-list-buttons">
                                <a href="#" class="mvv-item-remove">
                                    <?= Icon::create('trash', Icon::ROLE_CLICKABLE, ['title' => _('Fach entfernen')])->asImg(); ?>
                                </a>
                            </div>
                        <? endif; ?>
                        <input type="hidden" name="fach_item" value="<?= $stgteil->fach->id ?>">
                    </li>
                <? endif; ?>
            </ul>
        <? endif; ?>
    </fieldset>

    <fieldset class="collapsable collapsed">
        <legend>
            <?= _('Ausprägung') ?>
        </legend>
        <label><?= _('Kredit-Punkte') ?>
            <input <?= $perm->disable('kp') ?>
                    type="text" name="kp" id="stgteil_kp" size="10" maxlength="50"
                    value="<?= htmlReady($stgteil->kp) ?>">
        </label>
        <label>
            <?= _('Semesterzahl') ?>
            <? if ($perm->haveFieldPerm('semester')) : ?>
                <select name="semester" id="stgteil_semester">
                    <option value="">--</option>
                    <? for ($sem = 1; $sem < 21; $sem++) : ?>
                        <option value="<?= $sem ?>"<?= ((int) $stgteil->semester === $sem ? ' selected' : '') ?>><?= $sem ?></option>
                    <? endfor; ?>
                </select>
            <? else : ?>
                <?= htmlReady($stgteil->semester) ?>
                <input type="hidden" name="semester" value="<?= $stgteil->semester ?>">
            <? endif; ?>
        </label>
    </fieldset>

    <fieldset class="collapsable collapsed">
        <legend>
        <?= _('Titelzusatz') ?>
        </legend>
        <?= MvvI18N::input('zusatz', $stgteil->zusatz, ['id' => 'stgteil_zusatz', 'maxlength' => '200'])->checkPermission($stgteil) ?>
    </fieldset>

    <fieldset class="collapsable collapsed">
        <legend>
            <?= _('Ansprechpartner'); ?>
        </legend>
        <?= $this->render_partial('shared/contacts/range', ['perm_contacts' => $perm->haveFieldPerm('contact_assignments', MvvPerm::PERM_CREATE), 'range_type' => 'StudiengangTeil', 'range_id' => $stgteil->id]) ?>
    </fieldset>
    
    <footer data-dialog-button>
        <? if ($stgteil->isNew()) : ?>
            <? if ($perm->havePermCreate()) : ?>
                <?= Button::createAccept(_('Anlegen'), 'store', ['title' => _('Abschluss anlegen')]) ?>
            <? endif; ?>
        <? else : ?>
            <? if ($perm->havePermWrite()) : ?>
                <?= Button::createAccept(_('Übernehmen'), 'store', ['title' => _('Änderungen übernehmen')]) ?>
            <? endif; ?>
        <? endif; ?>
        <?= LinkButton::createCancel(_('Abbrechen'), $cancel_url, ['title' => _('zurück zur Übersicht')]) ?>
    </footer>
</form>
