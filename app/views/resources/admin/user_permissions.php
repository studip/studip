<? if ($user): ?>
    <div>
        <dl>
            <dt><?= _('Globale Berechtigung') ?></dt>
            <dd>
                <? if ($global_permission): ?>
                    <?= htmlReady($global_permission->perms) ?>
                    <? if ($current_global_lock and ($global_permission->perms != 'admin')): ?>
                        <?= Icon::create('exclaim', 'attention')->asImg(
                            [
                                'class' => 'text-bottom',
                                'title' => _('Die Berechtigung kann zurzeit aufgrund einer globalen Sperrung der Raumverwaltung nicht genutzt werden!')
                            ]
                        )?>
                    <? endif ?>
                <? else: ?>
                    <?= _('keine') ?>
                <? endif ?>
            </dd>
            <dt><?= _('Aktivität') ?></dt>
            <dd title="<?= htmlReady($last_activity_date) ?>">
                <? if ($last_activity): ?>
                    <? if ($last_activity->y): ?>
                        <?= sprintf(
                            ngettext(
                                'Inaktiv seit mehr als einem Jahr',
                                'Inaktiv seit mehr als %d Jahren',
                                $last_activity->y
                            ),
                            $last_activity->y
                        ) ?>
                    <? elseif ($last_activity->m): ?>
                        <?= sprintf(
                            ngettext(
                                'Inaktiv seit mehr als einem Monat',
                                'Inaktiv seit mehr als %d Monaten',
                                $last_activity->m
                            ),
                            $last_activity->m
                        ) ?>
                    <? elseif ($last_activity->d): ?>
                        <? if ($last_activity->d == 1): ?>
                            <?= _('Gestern zuletzt aktiv.') ?>
                        <? elseif ($last_activity->d == 2): ?>
                            <?= _('Vorgestern zuletzt aktiv.') ?>
                        <? else: ?>
                            <?= sprintf(
                                _('Inaktiv seit %d Tagen'),
                                $last_activity->d
                            ) ?>
                        <? endif ?>
                    <? else: ?>
                        <?= _('Innerhalb der letzten 24 Stunden zuletzt aktiv gewesen.') ?>
                    <? endif ?>
                <? elseif ($last_activity === null): ?>
                    <?= _('keine') ?>
                <? else: ?>
                    <?= _('Fehler') ?>
                <? endif ?>
            </dd>
        </dl>
        <? if ($last_activity !== null): ?>
            <?= \Studip\LinkButton::create(
                _('Liste mit Buchungen anzeigen'),
                URLHelper::getLink(
                    'dispatch.php/resources/admin/booking_log/'
                  . $user->id
                ),
                [
                    'data-dialog' => '1'
                ]
            ) ?>
        <? endif ?>
    </div>
    <? if ($temporary_permissions): ?>
        <table class="default resources_permissions-table sortable-table"
               data-sortlist="[[0, 0]]">
            <caption>
                <?= _('Temporäre Berechtigungen') ?>
            </caption>
            <thead>
                <tr>
                    <th data-sort="text"><?= _('Name der Ressource') ?></th>
                    <th data-sort="1"><?= _('Berechtigung') ?></th>
                    <th><?= _('Gültigkeit') ?></th>
                    <th class="actions"><?= _('Aktionen') ?></th>
                </tr>
            </thead>
            <tbody>
                <? foreach ($temporary_permissions as $permission): ?>
                    <tr>
                        <td>
                            <?= htmlReady($permission->resource->getDerivedClassInstance()) ?>
                        </td>
                        <td>
                            <?= htmlReady($permission->perms) ?>
                            <? if ($current_global_lock and ($permission->perms != 'admin')): ?>
                                <?= Icon::create('exclaim', 'attention')->asImg(
                                    '20px',
                                    [
                                        'class' => 'text-bottom',
                                        'title' => _('Die Berechtigung kann aufgrund einer globalen Sperrung der Raumverwaltung zurzeit nicht genutzt werden!')
                                    ]
                                )?>
                            <? endif ?>
                        </td>
                        <td>
                            <?= date('d.m.Y H:i', $permission->begin) ?>
                            -
                            <?= date('d.m.Y H:i', $permission->end) ?>
                        </td>
                        <td class="actions">
                            <a href="<?= $permission->resource->getActionLink(
                                     'temporary_permissions',
                                     [
                                         'user_id' => $permission->user_id
                                     ]
                                     ) ?>" data-dialog>
                                <?= Icon::create('edit')->asImg(
                                    '20px',
                                    [
                                        'class' => 'text-bottom',
                                        'title' => _('Berechtigung bearbeiten')
                                    ]
                                ) ?>
                            </a>
                            <a href="<?= URLHelper::getLink(
                                     'dispatch.php/resources/admin/booking_log/'
                                     . $user->id
                                     . '/'
                                     . $permission->resource_id
                                     ) ?>" data-dialog>
                                <?= Icon::create('log')->asImg(
                                    [
                                        'class' => 'text-bottom',
                                        'title' => 'Liste mit Buchungen anzeigen'
                                    ]
                                ) ?>
                            </a>
                        </td>
                    </tr>
                <? endforeach ?>
            </tbody>
        </table>
    <? endif ?>
        <? if ($permissions): ?>
        <table class="default resources_permissions-table sortable-table"
               data-sortlist="[[0, 0]]">
            <caption>
                <?= _('Dauerhafte Berechtigungen') ?>
            </caption>
            <thead>
                <tr>
                    <th data-sort="text"><?= _('Name der Ressource') ?></th>
                    <th data-sort="text"><?= _('Berechtigung') ?></th>
                    <th class="actions"><?= _('Aktionen') ?></th>
                </tr>
            </thead>
            <tbody>
                <? foreach ($permissions as $permission): ?>
                    <tr>
                        <td>
                            <?= htmlReady($permission->resource->getDerivedClassInstance()) ?>
                        </td>
                        <td>
                            <?= htmlReady($permission->perms) ?>
                            <? if ($current_global_lock and ($permission->perms != 'admin')): ?>
                                <?= Icon::create('exclaim', 'attention')->asImg(
                                    [
                                        'class' => 'text-bottom',
                                        'title' => _('Die Berechtigung kann zurzeit aufgrund einer globalen Sperrung der Raumverwaltung nicht genutzt werden!')
                                    ]
                                )?>
                            <? endif ?>
                        </td>
                        <td class="actions">
                            <a href="<?= $permission->resource->getActionLink(
                                     'permissions',
                                     [
                                         'user_id' => $permission->user_id
                                     ]
                                     ) ?>" data-dialog>
                                <?= Icon::create('edit')->asImg(
                                    [
                                        'class' => 'text-bottom',
                                        'title' => _('Berechtigung bearbeiten')
                                    ]
                                ) ?>
                            </a>
                            <a href="<?= URLHelper::getLink(
                                     'dispatch.php/resources/admin/booking_log/'
                                     . $user->id
                                     . '/'
                                     . $permission->resource_id
                                     ) ?>" data-dialog>
                                <?= Icon::create('log')->asImg(
                                    [
                                        'class' => 'text-bottom',
                                        'title' => _('Liste mit Buchungen anzeigen')
                                    ]
                                ) ?>
                            </a>
                        </td>
                    </tr>
                <? endforeach ?>
            </tbody>
        </table>
    <? endif ?>
<? else: ?>
    <?= MessageBox::info(
        _('Es wurde keine Person ausgewählt!')
    ) ?>
<? endif ?>
