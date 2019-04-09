<? use Studip\Button, Studip\LinkButton; ?>
<div class="modaloverlay">
    <div class="messagebox">
        <div class="content">
            <?= formatReady($question) ?>
        </div>
        <div class="buttons">
            <form action="<?=$action ?>" method="post">
            <?= CSRFProtection::tokenTag() ?>
                <?= Button::createAccept(_('JA!'), 'kill', ['title' => _('Raumanfrage löschen')])?>
                <span style="margin-left: 1em;">
                    <?= Button::createCancel(_('NEIN!'), 'cancel', ['title' => _('Raumanfrage löschen')])?>
                </span>
            </form>
        </div>
    </div>
</div>
