<? use Studip\Button, Studip\LinkButton; ?>
<?= $controller->jsUrl() ?>
<script>
    MVV.PARENT_ID = '<?= $abschluss_kategorie->getId() ?>';
    MVV.OBJECT_TYPE = 'AbschlussKategorie';
</script>
<? $perm = MvvPerm::get($abschluss_kategorie) ?>
<form class="default"
      action="<?= $controller->url_for('fachabschluss/kategorien/kategorie/' . $abschluss_kategorie->getId()) ?>"
      method="post">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= _('Grunddaten') ?></legend>
        <label>
            <?= _('Name') ?>
            <?= MvvI18N::input(
                'name',
                $abschluss_kategorie->name,
                ['maxlength' => '255', 'required' => '']
            )->checkPermission($abschluss_kategorie) ?>
            <label>
                <?= _('Kurzname') ?>
                <?= MvvI18N::input(
                    'name_kurz',
                    $abschluss_kategorie->name_kurz,
                    ['maxlength' => '50']
                )->checkPermission($abschluss_kategorie) ?>
            </label>
            <label>
                <?= _('Beschreibung') ?>
                <?= MvvI18N::textarea(
                    'beschreibung',
                    $abschluss_kategorie->beschreibung, ['class' => 'add_toolbar ui-resizable wysiwyg']
                )->checkPermission($abschluss_kategorie) ?>
            </label>
    </fieldset>
    <?= $this->render_partial(
        'shared/form_dokumente',
        ['perm_dokumente' => $perm->haveFieldPerm('document_assignments', MvvPerm::PERM_CREATE)]
    ) ?>
    <footer data-dialog-buttons>
        <? if ($abschluss_kategorie->isNew()) : ?>
            <? if ($perm->havePermCreate()) : ?>
                <?= Button::createAccept(_('Anlegen'), 'store', ['title' => _('Abschluss-Kategorie anlegen')]) ?>
            <? endif; ?>
        <? else : ?>
            <? if ($perm->havePermWrite()) : ?>
                <?= Button::createAccept(_('Übernehmen'), 'store', ['title' => _('Änderungen übernehmen')]) ?>
            <? endif; ?>
        <? endif; ?>
        <?= LinkButton::createCancel(
            _('Abbrechen'),
            $controller->url_for('fachabschluss/kategorien'),
            ['title' => _('zurück zur Übersicht')]
        ) ?>
    </footer>
</form>
