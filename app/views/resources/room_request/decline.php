<? if ($show_form): ?>
    <form class="default" method="post"
          action="<?= URLHelper::getLink(
                  'dispatch.php/resources/room_request/decline/' . $request->id
                  ) ?>"
          data-dialog>
        <?= CSRFProtection::tokenTag() ?>
        <? if ($delete_mode): ?>
            <input type="hidden" name="delete" value="1">
            <?= MessageBox::warning(
                _('Soll die folgende Anfrage wirklich gelöscht werden?')
            ) ?>
        <? else: ?>
            <?= MessageBox::warning(
                _('Soll die folgende Anfrage wirklich abgelehnt werden?')
            ) ?>
        <? endif ?>
        <fieldset>
            <legend><?= _('Daten zur Anfrage') ?></legend>
            <?= $this->render_partial(
                'resources/room_request/index',
                [
                    'request' => $request
                ]
            )?>
        </fieldset>
        <? if ($delete_mode): ?>
            <div data-dialog-button>
                <?= \Studip\Button::create(_('Löschen'), 'confirm') ?>
            </div>
        <? else: ?>
            <fieldset>
                <legend><?= _('Kommentar zur Ablehnung der Anfrage') ?></legend>
                <textarea name="reply_comment"><?= $reply_comment ?></textarea>
            </fieldset>
            <div data-dialog-button>
                <?= \Studip\Button::create(_('Ablehnen'), 'confirm') ?>
            </div>
        <? endif ?>
    </form>
<? endif ?>
