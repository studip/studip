<form class="default" method="post" id="decline-request"
      action="<?= $controller->link_for('resources/room_request/decline/' . $request->id) ?>"
      data-dialog>
    <?= CSRFProtection::tokenTag() ?>
    <? if ($show_form): ?>
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
            <legend><?= _('Informationen zur Anfrage') ?></legend>
            <?= $this->render_partial(
                'resources/room_request/index',
                [
                    'request' => $request
                ]
            ) ?>
        </fieldset>
        <? if (!$delete_mode): ?>
            <fieldset>
                <legend><?= _('Kommentar zur Ablehnung der Anfrage') ?></legend>
                <textarea name="reply_comment"><?= $reply_comment ?></textarea>
            </fieldset>
        <? endif ?>
    <? endif ?>
    <footer data-dialog-button>
        <? if ($prev_request) : ?>
            <?= \Studip\LinkButton::create(
                _('Vorherige Anfrage'),
                $controller->declineURL($prev_request),
                ['data-dialog' => 'size=big']
            ) ?>
        <? endif ?>
        <?= \Studip\LinkButton::create(
            _('Zurück'),
            $controller->resolveURL($request->id),
            ['data-dialog' => 'size=big']
        ) ?>
        <? if ($show_form) : ?>
            <?= \Studip\Button::createAccept($delete_mode ? _('Löschen') : _('Ablehnen'), 'confirm') ?>
        <? endif ?>
        <? if ($next_request) : ?>
            <?= \Studip\LinkButton::create(
                _('Nächste Anfrage'),
                $controller->declineURL($next_request),
                ['data-dialog' => 'size=big']
            ) ?>
        <? endif ?>
    </footer>
</form>