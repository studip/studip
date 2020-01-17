<? $perm = MvvPerm::get($aufbaustg); ?>
<form data-dialog action="<?= $controller->url_for('studiengaenge/studiengaenge/aufbaustg_store') ?>" class="default" id="mvv-aufbaustg-new" method="post">
    <input type="hidden" name="aufbaustg_id" value="<?= $aufbaustg->id ?>">
    <label>
        <?= _('Typ des Aufbaustudiengangs') ?>
        <select name="aufbaustg_typ" class="nested-select"<?= $perm->haveFieldPerm('typ', MvvPerm::PERM_WRITE) ? '' : ' disabled' ?>>
            <option class="is-placeholder"><?= _('Typ auswählen') ?></option>
        <? foreach ($GLOBALS['MVV_AUFBAUSTUDIENGANG']['TYP']['values'] as $key => $aufbaustg_typ) : ?>
            <? if ($aufbaustg_typ['visible']) : ?>
            <option value="<?= $key ?>"<?= $key == $aufbaustg->typ ? ' selected' : '' ?>><?= htmlReady($aufbaustg_typ['name']) ?></option>
            <? endif; ?>
        <? endforeach; ?>
        </select>
    </label>
    <label>
        <?= _('Bemerkung') ?>
        <?= MvvI18N::textarea('aufbaustg_kommentar', $aufbaustg->kommentar, ['class' => 'wysiwyg'])->checkPermission($aufbaustg) ?>
    </label>
    <div data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern'), 'store_aufbaustg') ?>
        <?= Studip\LinkButton::createCancel(); ?>
    </div>
</form>
