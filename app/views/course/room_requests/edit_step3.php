<? if ($available_rooms): ?>
    <h2><?= _('Passende Räume') ?></h2>
    <section class="selectbox">
        <fieldset>
            <? foreach ($available_rooms as $room): ?>
                <div class="flex-row">
                    <label class="horizontal">
                        <? if ($overlaps[$room->id] <= 0.0): ?>
                            <?= Icon::create(
                                'check-circle',
                                Icon::ROLE_STATUS_GREEN
                            )->asImg(
                                [
                                    'class' => 'text-bottom'
                                ]
                            ) ?>
                        <? elseif ($overlaps[$room->id] >= 1.0): ?>
                            <?= Icon::create(
                                'decline-circle',
                                Icon::ROLE_STATUS_RED
                            )->asImg(
                                [
                                    'class' => 'text-bottom'
                                ]
                            ) ?>
                        <? else: ?>
                            <?= Icon::create(
                                'exclaim-circle', Icon::ROLE_STATUS_YELLOW
                            )->asImg(
                                [
                                    'class' => 'text-bottom'
                                ]
                            ) ?>
                        <? endif ?>
                        <input type="radio" name="selected_room_id"
                               value="<?= htmlReady($room->id) ?>">
                        <?= htmlReady(mb_substr($room->name, 0, 50)); ?>
                        <? if ($room->properties): ?>
                            <? $property_names = $room->properties
                                ->findBy('info_label', 1)
                                ->findBy('state', '', '!=')
                                ->pluck('fullname') ?>
                            <?= tooltipIcon(
                                implode("\n", $property_names)
                            ) ?>
                        <? endif ?>
                    </label>
                </div>
            <? endforeach ?>
        </fieldset>
    </section>
<? elseif ($search_rooms) : ?>
    <?= MessageBox::info(
        _('Es wurden keine passenden Räume gefunden!')
    ) ?>
<? endif ?>
<?= Studip\Button::create(
    _("Anfragen"),
    'select_room'
) ?>
<?= Studip\Button::create(
    _("Neue Suche starten"),
    'reset_search'
) ?>
