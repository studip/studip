<? if ($show_question): ?>
    <form class="default" method="post"
          action="<?= URLHelper::getLink(
                  'dispatch.php/resources/booking/delete/' . $booking->id
                  ) ?>"
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
            <?= \Studip\Button::create(
                _('Löschen'),
                'confirm'
            ) ?>
        </div>
    </form>
<? endif ?>
