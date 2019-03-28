<? use Studip\Button, Studip\LinkButton; ?>
<form data-dialog="close" class="default mvv-form">
    <fieldset>
        <legend><?= _('Zusätzliche Anmerkungen/Kommentare zur Zuordnung') ?></legend>
        <label>
            <?= _('Kommentar') ?>
            <?= _('Beschreibung') ?>
            <?= MvvI18N::textarea('kommentar', $relation->kommentar, ['class' => 'add_toolbar ui-resizable wysiwyg', 'data-id' => $relation->dokument_id])->checkPermission($relation) ?>
        </label>
        <?= _('Die Änderungen werden erst gespeichert, wenn das Hauptformular gespeichert wurde!') ?>
    </fieldset>
    <footer data-dialog-button>
        <?= LinkButton::createAccept(_('Übernehmen'), '#dokumente_' . $relation->dokument_id, ['title' => _('Änderungen übernehmen')]) ?>
        <?= LinkButton::createCancel(_('Abbrechen'), '', ['title' => _('Bearbeitung abbrechen')]) ?>
    </footer>
</form>
