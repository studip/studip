<form class="default" method="post"
          action="<?= URLHelper::getLink(
                  'dispatch.php/resources/room_request/rerequest_booking/' . $booking->id
                  ) ?>"
          data-dialog="reload-on-close">
        <?= CSRFProtection::tokenTag() ?>
        <?= MessageBox::warning(
            _('Soll die folgende Buchung wirklich gelöscht werden?')
        ) ?>
        <?= $this->render_partial(
            'resources/booking/index',
            [
                'booking' => $booking,
                'hide_buttons' => true
            ]
        ) ?>
        <div data-dialog-button>
            <?= \Studip\Button::create(
                _('Löschen und Anfrage erstellen'),
                'delete_confirm'
            ) ?>
        </div>
    </form>
