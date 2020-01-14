<? if ($rooms): ?>
    <table class="default sortable-table rooms-list">
        <thead>
            <tr>
                <th data-sort="text"><?= _('Name') ?></th>
                <th data-sort="number"><?= _('Sitzplätze') ?></th>
                <th data-sort="text"><?= _('Raumkategorie') ?></th>
                <th data-sort="text"><?= _('Raumtyp') ?></th>
                <th class="actions"><?= _('Aktionen') ?></th>
            </tr>
        </thead>
        <tbody>
            <? foreach ($rooms as $room): ?>
                <?= $this->render_partial(
                    'resources/_common/_room_tr.php',
                    [
                        'room' => $room,
                        'show_global_admin_actions' => $show_global_admin_actions,
                        'show_admin_actions' => $room->userHasPermission(
                            $user,
                            'admin'
                        ),
                        'show_tutor_actions' => $room->userHasPermission(
                            $user,
                            'tutor'
                        ),
                        'show_autor_actions' => $room->userHasPermission(
                            $user,
                            'autor'
                        ),
                        'show_user_actions' => $room->userHasPermission(
                            $user,
                            'user'
                        ),
                        'user_has_booking_rights' => $room->userHasBookingRights(
                            $user
                        ),
                        'show_room_picture' => true,
                        'additional_columns' => [
                            $room->category->name,
                            $room->room_type,
                        ]
                    ]
                ) ?>
            <? endforeach ?>
        </tbody>
    </table>
<? endif ?>
