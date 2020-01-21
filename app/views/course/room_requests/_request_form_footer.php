<footer data-dialog-button>
    <? if ($room_selection_button) : ?>
        <?= \Studip\Button::create(_('Zur Raumauswahl'), 'select_room') ?>
    <? endif ?>
    <? if ($room_select_button) : ?>
        <?= \Studip\Button::create(_('Raum auswählen'), 'select_room') ?>
    <? endif ?>
    <? if ($save_buttons) :  ?>
        <?= \Studip\Button::create(_('Speichern'), 'save') ?>
        <?= \Studip\Button::create(_('Speichern und zurück zur Übersicht'), 'save_and_close') ?>
    <? endif ?>
    <? if ($select_properties_button) : ?>
        <?= \Studip\Button::create(_('Eigenschaften wählen'), 'select_properties') ?>
    <? endif ?>
    <?= \Studip\LinkButton::createCancel(
        _('Abbrechen'),
        $controller->link_for('course/room_requests/index/' . $course_id),
        [
            'title' => _('Abbrechen')
        ]
    ) ?>
</footer>
</form>
