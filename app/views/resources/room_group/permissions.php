<? if ($rooms): ?>
    <table class="default">
        <caption><?= _('Räume') ?></caption>
        <thead>
            <tr>
                <th><?= _('Name') ?></th>
                <th><?= _('Sitzplätze') ?></th>
                <th class="actions"><?= _('Aktionen') ?></th>
            </tr>
        </thead>
        <tbody>
            <? foreach ($rooms as $room): ?>
                <?= $this->render_partial(
                    'resources/_common/_room_tr.php',
                    [
                        'room' => $room,
                        //The permissions view is only accessible
                        //for admin users.
                        'show_user_actions' => false,
                        'show_autor_actions' => false,
                        'show_tutor_actions' => false,
                        'show_admin_actions' => false
                    ]
                ) ?>
            <? endforeach ?>
        </tbody>
    </table>
    <? if ($show_form): ?>
        <?= $this->render_partial(
            'resources/resource/permissions',
            [
                'custom_empty_list_message' => _('Es sind keine gemeinsamen Rechte für die oben aufgeführten Räume vorhanden.'),
                'custom_save_button_text' => _('Zuweisen'),
                'custom_form_action_link' => URLHelper::getLink('dispatch.php/resources/room_group/permissions/' . $clipboard->id),
                'custom_hidden_fields' => [
                    'resource_ids[]' => $room_ids
                ],
                'permissions' => $common_permissions,
                'table_caption' => _('Gemeinsame Rechte'),
                'table_id' => 'RoomGroupCommonPermissionTable',
                'user_search' => $user_search
            ]
        ) ?>
        <? if ($partial_permissions): ?>
            <?= $this->render_partial(
                'resources/_common/_permission_table.php',
                [
                    'permissions' => $partial_permissions,
                    'custom_columns' => [
                        _('Raum') => $permission_room_list
                    ],
                    'custom_actions' => [
                        [
                            'icon' => Icon::create('arr_2up'),
                            'title' => _('Berechtigung für alle Räume übernehmen'),
                            'link_classes' => 'apply-to-all-action'
                        ]
                    ],
                    'show_delete_action' => false,
                    'table_caption' => _('Spezielle Rechte'),
                    'user_search' => $user_search
                ]
            ) ?>
        <? endif ?>
    <? endif ?>
<? endif ?>
