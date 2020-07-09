<article class="studip room-list-item">
    <header class="widget-header">
        <h1><?= Assets::img(
            'anfasser_24.png',
            [
                'class' => 'clipboard-draggable-item',
                'data-id' => $room->id,
                'data-range_type' => 'Room',
                'data-name' => $room->name
            ]
        ) ?><?= htmlReady($room->name) ?></h1>
        <?
        $actions = ActionMenu::get();
        $actions->addLink(
            $room->getActionLink('show'),
            _('Raumdetails anzeigen'),
            Icon::create('info-circle'),
            ['data-dialog' => '']
        );
        if ($room->userHasPermission($current_user, 'autor')) {
            $actions->addLink(
                $room->getActionLink('booking_plan'),
                _('Wochenbelegung'),
                Icon::create('timetable'),
                ['target' => '_blank']
            );
            $actions->addLink(
                $room->getActionLink('semester_plan'),
                _('Semesterbelegung'),
                Icon::create('timetable'),
                ['target' => '_blank']
            );
        } else {
            $actions->addLink(
                $room->getActionLink('booking_plan'),
                _('Belegungsplan'),
                Icon::create('timetable'),
                ['data-dialog' => 'size=big']
            );
            $actions->addLink(
                $room->getActionLink('semester_plan'),
                _('Semesterbelegung'),
                Icon::create('timetable'),
                ['data-dialog' => 'size=big']
            );
        }
        if ($room->requestable && $room->userHasRequestRights($current_user)) {
            $actions->addLink(
                $room->getActionLink('request'),
                _('Raum anfragen'),
                Icon::create('room-request'),
                ['data-dialog' => 'size=auto']
            );
        }
        if ($room->building) {
            $actions->addLink(
                ResourceManager::getMapUrlForResourcePosition(
                    $room->building->getPropertyObject('geo_coordinates')
                ),
                _('Zum Lageplan'),
                Icon::create('globe'),
                ['target' => '_blank']
            );
        }
        if ($clipboard_widget_id) {
            $actions->addLink(
                '#',
                _('Zur Raumgruppe hinzufügen'),
                IcoN::create('add'),
                [
                    'class' => 'clipboard-add-item-button',
                    'data-range_type' => 'Room',
                    'data-range_id' => $room->id,
                    'data-clipboard_id' => $clipboard_widget_id
                ]
            );
        }
        ?>
        <?= $actions->render() ?>
    </header>
    <section>
        <p class="description">
                <?= htmlReady($room->description) ?>
        </p>
        <section class="properties-and-actions">
            <ul class="property-list">
                <? if ($room->room_type): ?>
                    <li><?= htmlReady($room->room_type) ?></li>
                <? endif ?>
                <? if ($room->seats): ?>
                    <li>
                        <?= sprintf(
                            ngettext(
                                '%d Sitzplatz',
                                '%d Sitzplätze',
                                $room->seats
                            ),
                            $room->seats
                        ) ?>
                    </li>
                <? endif ?>
            </ul>
        </section>
    </section>
</article>
