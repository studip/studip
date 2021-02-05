<? if ($show_question): ?>
    <form class="default" method="post" action="<?= $controller->link_for('resources/booking/delete/' . $booking->id) ?>"
          data-dialog="reload-on-close">
        <?= CSRFProtection::tokenTag() ?>
        <?= MessageBox::warning(
            _('Soll die folgende Buchung wirklich gelöscht werden?')
        ) ?>
        <? if ($show_details): ?>
            <?= $this->render_partial(
                'resources/booking/index',
                [
                    'booking' => $booking,
                    'hide_buttons' => true
                ]
            ) ?>
        <? endif ?>
        <div data-dialog-button>
            <?= \Studip\LinkButton::create(
                _('Zurück'),
                $controller->url_for('resources/booking/edit/' . $booking->id),
                [
                    'data-dialog' => '1'
                ]
            ) ?>
            <?= \Studip\Button::create(
                _('Löschen'),
                'confirm'
            ) ?>
        </div>
    </form>
<? endif ?>
