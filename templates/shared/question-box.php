<div class="modaloverlay">
    <div class="ui-dialog ui-corner-all ui-widget ui-widget-content ui-front ui-dialog-buttons studip-confirmation">
        <form action="<?= URLHelper::getLink($accept_url) ?>" method="post">
            <?= CSRFProtection::tokenTag() ?>
        <? foreach ($accept_parameters as $key => $value): ?>
            <?= addHiddenFields($key, $value) ?>
        <? endforeach; ?>

            <div class="ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix">
                <span class="ui-dialog-title"><?= _('Bitte bestätigen Sie die Aktion') ?></span>
                <a href="<?= URLHelper::getLink($decline_url, $decline_parameters) ?>" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only ui-dialog-titlebar-close">
                    <span class="ui-button-icon-primary ui-icon ui-icon-closethick"></span>
                    <span class="ui-button-text"><?= _('Schliessen') ?></span>
                </a>
            </div>
            <div class="content ui-widget-content ui-dialog-content studip-confirmation">
                <?= $question ?>
            </div>
            <div class="buttons ui-widget-content ui-dialog-buttonpane">
                <div class="ui-dialog-buttonset">
                    <?= Studip\Button::createAccept(_('Ja'), 'yes') ?>

                    <?= Studip\LinkButton::createCancel(
                        _('Nein'),
                        URLHelper::getURL($decline_url, $decline_parameters)
                    ) ?>
                </div>
            </div>
        </form>
    </div>
</div>
