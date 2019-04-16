<? use Studip\Button, Studip\LinkButton; ?>
<?= $controller->jsUrl() ?>
<? $perm = MvvPerm::get($stgteil) ?>

<form class="default" action="<?= $controller->url_for('/stgteil/' . $stgteil->id) ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= _('Fach') ?></legend>
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
    <fieldset>
        <legend><?= _('Ausprägung') ?></legend>
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
                        <option value="<?= $sem ?>"<?= ($stgteil->semester === $sem ? ' selected' : '') ?>><?= $sem ?></option>
                    <? endfor; ?>
                </select>
            <? else : ?>
                <?= htmlReady($stgteil->semester) ?>
                <input type="hidden" name="semester" value="<?= $stgteil->semester ?>">
            <? endif; ?>
        </label>
    </fieldset>
    <fieldset>
        <legend><?= _('Titelzusatz') ?></legend>
        <?= MvvI18N::input('zusatz', $stgteil->zusatz, ['id' => 'stgteil_zusatz', 'maxlength' => '200'])->checkPermission($stgteil) ?>
    </fieldset>
    <fieldset>
        <legend><?= _('Studienfachberater') ?></legend>
        <? if ($perm->haveFieldPerm('fachberater')) : ?>
            <?= $search_fachberater->render() ?>
            <? if (Request::submitted('search_fachberater')) : ?>
                <?= Icon::create(
                    'refresh',
                    Icon::ROLE_CLICKABLE,
                    [
                        'name'       => 'reset_fachberater',
                        'data-qs_id' => $search_fachberater_id
                    ])->asInput(); ?>
            <? else : ?>
                <?= Icon::create(
                    'search',
                    Icon::ROLE_CLICKABLE, [
                    'name'         => 'search_fachberater',
                    'data-qs_id'   => $search_fachberater_id,
                    'data-qs_name' => $search_fachberater->getId(),
                    'class'        => 'mvv-qs-button'
                ])->asInput(); ?>
            <? endif; ?>
        <? endif; ?>
        <ul id="fachberater_target" class="mvv-assigned-items sortable mvv-persons">
            <li class="mvv-item-list-placeholder"<?= (count($stgteil->fachberater) ? ' style="display:none;"' : '') ?>>
                <?= _('Studienfachberater zuordnen.') ?>
            </li>
            <? foreach ($stgteil->fachberater as $fachberater) : ?>
                <li id="fachberater_<?= $fachberater->getId() ?>"<?= $perm->haveFieldPerm('fachberater_assignments') ? 'class="sort_items"' : '' ?>>
                    <div class="mvv-item-list-text">
                        <a href="<?= URLHelper::getLink('dispatch.php/profile', ['username' => $fachberater->username]) ?>">
                            <?= htmlReady($fachberater->getFullname()) ?>
                        </a>
                    </div>
                    <? if ($perm->haveFieldPerm('fachberater')) : ?>
                        <div class="mvv-item-list-buttons">
                            <a href="#" class="mvv-item-remove">
                                <?= Icon::create('trash', Icon::ROLE_CLICKABLE ,['title' => _('Studienfachberater entfernen')])->asImg(); ?>
                            </a>
                        </div>
                    <? endif; ?>
                    <input type="hidden" name="fachberater_items[]" value="<?= $fachberater->id ?>">
                </li>
            <? endforeach; ?>
        </ul>
        <div style="width: 100%; max-width: 48em;">
            <?= _('Die Reihenfolge der Studienfachberater kann durch Anklicken und Ziehen geändert werden.') ?>
        </div>
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
