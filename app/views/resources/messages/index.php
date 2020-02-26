<form class="default resources_messages-form" method="post" data-dialog="size=auto"
      action="<?= URLHelper::getLink('dispatch.php/resources/messages/index') ?>">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= _('Raumauswahl') ?></legend>
        <label>
            <input type="radio" name="room_selection" value="search"
                   <?= ($room_selection == 'search' or !$clipboards)
                     ? 'checked="checked"'
                     : '' ?>>
            <?= _('Auswahl anhand von Suchergebnissen') ?>
            <?
            if ($room_selection != 'search') {
                $room_search->setAttributes(
                    [
                        'disabled' => 'disabled'
                    ]
                );
            }
            ?>
            <?= $room_search->render() ?>
            <div class="selection-area">
                <span class="selected-room template invisible">
                    <input type="hidden" name="room_ids[]">
                    <span></span>
                    <?= Icon::create('trash')->asImg(
                        [
                            'class' => 'remove-icon text-bottom'
                        ]
                    ) ?>
                </span>
                <? if ($selected_rooms): ?>
                    <? foreach ($selected_rooms as $room): ?>
                        <span class="selected-room">
                            <input type="hidden" name="room_ids[]"
                                   value="<?= htmlReady($room->id) ?>">
                            <span><?= htmlReady($room->name) ?></span>
                            <?= Icon::create('trash')->asImg(
                                [
                                    'class' => 'remove-icon text-bottom'
                                ]
                            ) ?>
                        </span>
                    <? endforeach ?>
                <? endif ?>
            </div>
        </label>
        <label>
            <input type="radio" name="room_selection" value="clipboard"
                   <?= ($room_selection == 'clipboard' and $clipboards)
                     ? 'checked="checked"'
                     : '' ?>
                   <?= !$clipboards ? 'disabled="disabled"' : '' ?>>
            <?= _('Auswahl anhand einer individuellen Raumgruppe') ?>
            <? if ($clipboards): ?>
                <select name="clipboard_id"
                        <?= $room_selection != 'clipboard'
                          ? 'disabled="disabled"'
                          : '' ?>>
                    <? foreach ($clipboards as $clipboard): ?>
                        <option value="<?= htmlReady($clipboard->id) ?>"
                                <?= $clipboard_id == $clipboard->id
                                  ? 'selected="selected"'
                                  : '' ?>>
                            <?= htmlReady($clipboard->name) ?>
                        </option>
                    <? endforeach ?>
                </select>
            <? endif ?>
        </label>
    </fieldset>
    <fieldset>
        <legend><?= _('Empfängerkreis auswählen') ?></legend>
        <label>
            <input type="radio" name="recipient_selection" value="permission"
                   <?= $recipient_selection == 'permission'
                     ? 'checked="checked"'
                     : '' ?>>
            <?= _('Alle Personen mit einer bestimmten Rechtestufe.') ?>
        </label>
        <label>
            <input type="radio" name="recipient_selection" value="booking"
                   <?= $recipient_selection == 'booking'
                     ? 'checked="checked"'
                     : '' ?>>
            <?= _('Alle Personen, die von Buchungen im folgenden Zeitraum betroffen sind.') ?>
        </label>
    </fieldset>
    <fieldset id="RecipientMode_Permission"
              <?= $recipient_selection != 'permission'
                ? 'style="display: none;"'
                : '' ?>>
        <legend>
            <?= _('Auswahl anhand der minimalen Rechtestufe') ?>
        </legend>
        <label>
            <?= _('Sende die Rundmail an alle Personen, die mindestens die folgende Rechtestufe an den ausgewählten Räumen haben:') ?>
            <select name="min_permission">
                <option value="user"
                        <?= $min_permission == 'user' ? 'selected="selected"' : ''?>>
                    user
                </option>
                <option value="autor"
                        <?= $min_permission == 'autor' ? 'selected="selected"' : ''?>>
                    autor
                </option>
                <option value="tutor"
                        <?= $min_permission == 'tutor' ? 'selected="selected"' : ''?>>
                    tutor
                </option>
                <option value="admin"
                        <?= $min_permission == 'admin' ? 'selected="selected"' : ''?>>
                    admin
                </option>
            </select>
        </label>
    </fieldset>
    <fieldset id="RecipientMode_Booking"
              <?= $recipient_selection != 'booking'
                ? 'style="display: none;"'
                : '' ?>>
        <legend>
            <?= _('Auswahl anhand von Buchungen in einem Zeitraum') ?>
        </legend>
        <label>
            <?= _('Beginn') ?>
            <input type="text" name="begin_date" class="has-date-picker"
                   value="<?= $begin->format('d.m.Y') ?>">
            <input type="text" name="begin_time" class="has-time-picker"
                   value="<?= $begin->format('H:i') ?>">
        </label>
        <label>
            <?= _('Ende') ?>
            <input type="text" name="end_date" class="has-date-picker"
                   value="<?= $end->format('d.m.Y') ?>">
            <input type="text" name="end_time" class="has-time-picker"
                   value="<?= $end->format('H:i') ?>">
        </label>
    </fieldset>
    <div data-dialog-button>
        <?= \Studip\Button::create(_('Mail verfassen'), 'write_mail') ?>
    </div>
</form>
