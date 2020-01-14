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
                            ]
                        ]
                    ) ?>
            <? endforeach ?>
            <tbody>
    </table>
<? endif ?>
