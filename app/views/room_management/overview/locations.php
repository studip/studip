<? if ($locations): ?>
    <table class="default">
        <thead>
            <tr>
                <th><?= _('Name') ?></th>
                <th><?= _('Adresse') ?></th>
                <th><?= _('Webseite') ?></th>
                <th class="actions"><?= _('Aktionen') ?></th>
            </tr>
        </thead>
        <tbody>
            <? foreach ($locations as $location): ?>
                    <?= $this->render_partial(
                        'resources/_common/_resource_tr.php',
                        [
                            'resource' => $location,
                            'booking_plan_link_on_name' => false,
                            'show_global_admin_actions' => $user_is_global_resource_admin,
                            'show_admin_actions' => $location->userHasPermission(
                                $user,
                                'admin'
                            ),
                            'show_tutor_actions' => $location->userHasPermission(
                                $user,
                                'tutor'
                            ),
                            'show_autor_actions' => $location->userHasPermission(
                                $user,
                                'autor'
                            ),
                            'show_user_actions' => $location->userHasPermission(
                                $user,
                                'user'
                            ),
                            'show_picture' => true,
                            'show_full_name' => false,
                            'additional_properties' => [
                                'address',
                                'website'
                            ],
                            'additional_actions' => [
                                '0020' => null,
                                '0030' => null,
                                '0040' => null,
                                '0050' => null,
                                '0070' => null,
                                '0080' => null,
                                '0090' => null
                            ]
                        ]
                    ) ?>
            <? endforeach ?>
            <tbody>
    </table>
<? endif ?>
