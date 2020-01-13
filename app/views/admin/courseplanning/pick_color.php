<form class="default" method="post" action="<?= $controller->pick_color($metadate_id, $from_action, $weekday) ?>" data-dialog="size=auto">
    <input type="hidden" id="selected-color" name="selected-color" value="<?= $color ?>">

    <div id="event-color-picker"></div>

    <input id="semtype-checkbox" name="event_color_semtype" type="checkbox" class="studip-checkbox" value="1">
    <label for="semtype-checkbox">
        <?= sprintf(_('Farbtyp für alle VA dieses Typs (%s) übernehmen'), htmlReady($semtype)) ?>
    </label>

    <div data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern'), 'save') ?>
    </div>
</form>
