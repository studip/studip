<? use Studip\Button, Studip\LinkButton; ?>
<? $perm = MvvPerm::get($abschluss) ?>
<form class="default" action="<?= $controller->url_for('/abschluss/' . $abschluss->getId()) ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= _('Grunddaten') ?></legend>
        <label>
            <?= _('Name') ?>
            <?= MvvI18N::input('name', $abschluss->name, ['maxlength' => '255', 'required' => ''])->checkPermission($abschluss) ?>
        </label>
        <label>
            <?= _('Kurzname') ?>
            <?= MvvI18N::input('name_kurz', $abschluss->name_kurz, ['maxlength' => '50'])->checkPermission($abschluss) ?>
        </label>
        <label>
            <?= _('Beschreibung') ?>
            <?= MvvI18N::textarea('beschreibung', $abschluss->beschreibung, ['class' => 'add_toolbar ui-resizable wysiwyg'])->checkPermission($abschluss) ?>
    </fieldset>
    <fieldset>
        <legend><?= _('Abschluss-Kategorie wählen') ?></legend>
        <? if ($perm->haveFieldPerm('category_assignment')) : ?>
            <label><?= _('Abschluss-Kategorie') ?></label>
            <select id="abschluss_kategorie" name="kategorie_id" size="1">
                <option value=""><?= _('-- bitte wählen --') ?></option>
                <? foreach ($abschluss_kategorien as $kategorie) : ?>
                    <option
                        <?= ($kategorie->getId() === $abschluss->kategorie_id ? 'selected ' : '') ?>value="<?= $kategorie->getId() ?>"><?= htmlReady($kategorie->name) ?></option>
                <? endforeach; ?>
            </select>
            </label>
        <? else : ?>
            <?= htmlReady(AbschlussKategorie::get($abschluss->kategorie_id)->getDisplayName()) ?>
        <? endif; ?>
    </fieldset>
    <footer data-dialog-button>
        <? if ($abschluss->isNew()) : ?>
            <? if ($perm->havePermCreate()) : ?>
                <?= Button::createAccept(_('Anlegen'), 'store', ['title' => _('Abschluss anlegen')]) ?>
            <? endif; ?>
        <? else : ?>
            <? if ($perm->havePermWrite()) : ?>
                <?= Button::createAccept(_('Übernehmen'), 'store', ['title' => _('Änderungen übernehmen')]) ?>
            <? endif; ?>
        <? endif; ?>
        <?= LinkButton::createCancel(_('Abbrechen'), $controller->url_for('/index'), ['title' => _('zurück zur Übersicht')]) ?>
    </footer>
</form>
