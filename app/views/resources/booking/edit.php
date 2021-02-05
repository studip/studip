<?= $this->render_partial('resources/booking/_add_edit_form') ?>

<div data-dialog-button>
    <?= \Studip\LinkButton::create(
        _('Duplizieren'),
        $controller->url_for('resources/booking/duplicate/' . $booking->id),
        [
            'data-dialog' => '1'
        ]
    ) ?>
    <?= \Studip\LinkButton::create(
        _('Kopieren'),
        $controller->url_for('resources/booking/copy/' . $booking->id),
        [
            'data-dialog' => 'size=auto'
        ]
    ) ?>
    <?= \Studip\LinkButton::create(
        _('Verschieben'),
        $controller->url_for('resources/booking/move/' . $booking->id),
        [
            'data-dialog' => 'size=auto'
        ]
    ) ?>
    <?= \Studip\LinkButton::create(
        _('Löschen'),
        $controller->url_for('resources/booking/delete/' . $booking->id),
        [
            'data-dialog' => '1'
        ]
    ) ?>
</div>

