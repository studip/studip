<?= $this->render_partial('resources/booking/_add_edit_form') ?>
<? if (Request::isDialog()): ?>
    <div data-dialog-button>
        <?= \Studip\LinkButton::create(
            _('Kopieren'),
            URLHelper::getURL(
                'dispatch.php/resources/booking/copy/' . $booking->id
            ),
            [
                'data-dialog' => '1'
            ]
        ) ?>
        <?= \Studip\LinkButton::create(
            _('Verschieben'),
            URLHelper::getURL(
                'dispatch.php/resources/booking/move/' . $booking->id
            ),
            [
                'data-dialog' => '1'
            ]
        ) ?>
        <?= \Studip\LinkButton::create(
            _('Löschen'),
            URLHelper::getURL(
                'dispatch.php/resources/booking/delete/' . $booking->id
            ),
            [
                'data-dialog' => '1'
            ]
        ) ?>
    </div>
<? endif ?>
