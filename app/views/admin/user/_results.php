<br>

<form action="<?= $controller->link_for('admin/user/bulk') ?>" method="post" data-dialog="size=auto" class="default">
    <?= CSRFProtection::tokenTag() ?>
    <table class="default">
        <caption>
            <?= sprintf(_('Suchergebnis: es wurden %s Personen gefunden'), count($users)) ?>
        </caption>
        <thead>
            <tr class="sortable">
                <th colspan="2" <?= $sortby === 'username' ? 'class="sort' . $order . '"' : '' ?>>
                    <a href="<?= $controller->link_for('admin/user', ['sortby' => 'username', 'order' => $order, 'toggle' => $sortby === 'username']) ?>">
                        <?= _('Benutzername') ?>
                    </a>
                </th>
                <th>&nbsp;</th>
                <th <?= $sortby === 'perms' ? 'class="sort' . $order . '"' : '' ?>>
                    <a href="<?= $controller->link_for('admin/user',['sortby' =>'perms', 'order'=> $order ,'toggle' => $sortby === 'perms']) ?>">
                        <?= _('Status') ?>
                    </a>
                </th>
                <th <?= $sortby === 'Vorname' ? 'class="sort' . $order . '"' : '' ?>>
                    <a href="<?= $controller->link_for('admin/user', ['sortby' => 'Vorname', 'order' => $order, 'toggle' => $sortby === 'Vorname']) ?>">
                        <?= _('Vorname') ?>
                    </a>
                </th>
                <th <?= $sortby === 'Nachname' ? 'class="sort' . $order . '"' : '' ?>>
                    <a href="<?= $controller->link_for('admin/user', ['sortby' => 'Nachname' , 'order' => $order, 'toggle' => $sortby === 'Nachname']) ?>">
                        <?= _('Nachname') ?>
                    </a>
                </th>
                <th <?= $sortby === 'Email' ? 'class="sort' . $order . '"' : '' ?>>
                    <a href="<?= $controller->link_for('admin/user', ['sortby' => 'Email', 'order' => $order, 'toggle' => $sortby === 'Email']) ?>">
                        <?= _('E-Mail') ?>
                    </a>
                </th>
                <th <?= $sortby === 'changed' ? 'class="sort' . $order . '"' : '' ?>>
                    <a href="<?= $controller->link_for('admin/user', ['sortby' => 'changed', 'order' => $order, 'toggle' => $sortby === 'changed']) ?>">
                        <?= _('inaktiv') ?>
                    </a>
                </th>
                <th <?= $sortby === 'mkdate' ? 'class="sort' . $order . '"' : '' ?>>
                    <a href="<?= $controller->link_for('admin/user', ['sortby' => 'mkdate', 'order' => $order , 'toggle' => $sortby === 'mkdate']) ?>">
                        <?= _('registriert seit') ?>
                    </a>
                </th>
                <th colspan="2" <?= $sortby === 'auth_plugin' ? 'class="sort' . $order . '"' : '' ?>>
                    <a href="<?= $controller->link_for('admin/user', ['sortby'=> 'auth_plugin', 'order' => $order ,'toggle' => $sortby === 'auth_plugin']) ?>">
                        <?= _('Authentifizierung') ?>
                    </a>
                </th>
            </tr>
        </thead>

        <tbody>

            <? foreach ($users as $user) : ?>
                <tr>
                    <td style="white-space:nowrap;">
                        <input class="check_all" type="checkbox" name="user_ids[]" value="<?= htmlReady($user->id) ?>">
                        <a href="<?= $controller->link_for("admin/user/edit/{$user->id}") ?>"
                           title="<?= _('Nutzer bearbeiten') ?>">
                            <?= Avatar::getAvatar($user->id)->getImageTag(Avatar::SMALL) ?>
                        </a>
                    </td>
                    <td>
                        <a href="<?= $controller->link_for("admin/user/edit/{$user->id}") ?>"
                           title="<?= _('Nutzer bearbeiten') ?>">
                            <?= htmlReady($user->username) ?>
                        </a>
                    <? if ($user->locked) : ?>
                        <?= Icon::create('lock-locked', Icon::ROLE_INFO)->asImg(tooltip2(sprintf(_('%s ist gesperrt'), htmlReady($user->getFullname())))) ?>
                    <? endif ?>
                    </td>
                    <td>
                        <?
                        $userdomains = UserDomain::getUserDomainsForUser($user->user_id);
                        $tooltxt     = _('Sichtbarkeit:') . ' ' . $user->visible;
                        if (!empty($userdomains)) {
                            $domains = [];
                            array_walk($userdomains, function ($a) use (&$domains) {
                                if (!in_array($a->name, $domains)) {
                                    $domains[] = $a->name;
                                }
                            });
                            $tooltxt .= "\n" . _('Domänen:') . ' ' . implode(', ', $domains);
                        }
                        if ($user->locked == '1') {
                            $tooltxt .= "\n" . _("Nutzer ist gesperrt!");
                        }
                        ?>
                        <?= tooltipHtmlIcon(htmlReady($tooltxt, true, true)) ?>
                    </td>
                    <td><?= $user['perms'] ?></td>
                    <td><?= htmlReady($user->Vorname) ?></td>
                    <td><?= htmlReady($user->nachname) ?></td>
                    <td><?= htmlReady($user->email) ?></td>
                    <td>
                        <? if ($user->online->last_lifesign) :
                            $inactive = time() - $user->online->last_lifesign;
                            if ($inactive < 3600 * 24) {
                                $inactive = gmdate('H:i:s', $inactive);
                            } else {
                                $inactive = floor($inactive / (3600 * 24)) . ' ' . _('Tage');
                            }
                        else :
                            $inactive = _('nie benutzt');
                        endif ?>
                        <?= $inactive ?>
                    </td>
                    <td>
                        <?= $user->mkdate ? strftime('%x', $user->mkdate) : _('unbekannt') ?>
                    </td>
                    <td><?= htmlReady($user['auth_plugin'] === null ? _('vorläufig') : $user->auth_plugin) ?></td>
                    <td class="actions" nowrap>
                    <?
                        $actionMenu = ActionMenu::get();
                        $actionMenu->addLink(
                            $controller->url_for("admin/user/edit/{$user->id}"),
                            _('Nutzer bearbeiten'),
                            Icon::create('edit')
                        );

                        $actionMenu->addLink(
                            $controller->url_for('profile',['username' => $user->username]),
                            _('Zum Profil'),
                            Icon::create('person')
                        );
                        if ($GLOBALS['perm']->have_perm('root')) {
                            $actionMenu->addLink(
                                $controller->url_for('admin/user/activities/' . $user->user_id, ['from_index' => 1]),
                                _('Datei- und Aktivitätsübersicht'),
                                Icon::create('vcard'),
                                ['data-dialog' => 'size=50%']
                            );
                            if (Config::get()->LOG_ENABLE) {
                                $actionMenu->addLink(
                                    $controller->url_for('event_log/show', ['search' => $user->username, 'type' => 'user', 'object_id' => $user->id]),
                                    _('Personeneinträge im Log'),
                                    Icon::create('log')
                                );
                            }
                        }

                        $actionMenu->addLink(
                            $controller->url_for('messages/write', ['rec_uname' => $user->username]),
                            _('Nachricht an Nutzer verschicken'),
                            Icon::create('mail'),
                            ['data-dialog' => 'size=auto']
                        );

                        if ($user->locked) {
                            $actionMenu->addLink(
                                $controller->url_for("admin/user/unlock/{$user->id}", ['from_index' => 1]),
                                _('Nutzeraccount entsperren'),
                                Icon::create('lock-unlocked')
                            );
                        } else {
                            $actionMenu->addLink(
                                $controller->url_for("admin/user/lock_comment/{$user->id}", ['from_index' => 1]),
                                _('Nutzeraccount sperren'),
                                Icon::create('lock-locked'),
                                ['data-dialog' => 'size=auto']
                            );
                        }

                        if ($user->auth_plugin !== 'preliminary' && ($GLOBALS['perm']->have_perm('root') || $GLOBALS['perm']->is_fak_admin() || !in_array($user->perms, words('root admin')))) {
                            if (!StudipAuthAbstract::CheckField('auth_user_md5.password', $user->auth_plugin)) {
                                $actionMenu->addLink(
                                    $controller->url_for("admin/user/change_password/{$user->id}", ['from_index' => 1]),
                                    _('Neues Passwort setzen'),
                                    Icon::create('key')
                                );
                            }

                            $actionMenu->addButton(
                                'delete_user',
                                _('Nutzer löschen'),
                                Icon::create('trash'),
                                ['formaction' => $controller->url_for("admin/user/bulk/{$user->id}", ['method' => 'delete'])]
                            );

                        }

                        if (Privacy::isVisible($user_id)) {
                            $actionMenu->addLink(
                                $controller->url_for("privacy/landing/{$user->id}"),
                                _('Anzeige Personendaten'),
                                Icon::create('log'),
                                ['data-dialog' => 'size=medium']
                            );
                            $actionMenu->addLink(
                                $controller->url_for("privacy/print/{$user->id}"),
                                _('Personendaten drucken'),
                                Icon::create('print'),
                                ['class' => 'print_action', 'target' => '_blank']
                            );
                            $actionMenu->addLink(
                                $controller->url_for("privacy/export/{$user->id}"),
                                _('Export Personendaten als CSV'),
                                Icon::create('file-text')
                            );
                            $actionMenu->addLink(
                                $controller->url_for("privacy/xml/{$user->id}"),
                                _('Export Personendaten als XML'),
                                Icon::create('file-text')
                            );
                            $actionMenu->addLink(
                                $controller->url_for("privacy/filesexport/{$user->id}"),
                                _('Export persönlicher Dateien als ZIP'),
                                Icon::create('file-archive')
                            );
                        }

                        echo $actionMenu;
                    ?>
                    </td>
                </tr>
            <? endforeach ?>

        </tbody>
        <tfoot>
            <tr>
                <td colspan="11" align="right">
                        <input style="vertical-align: middle" type="checkbox" name="check_all" title="<?= _('Alle Benutzer auswählen') ?>"
                               data-proxyfor=".check_all" data-activates=".bulkAction">
                        <select name="method" class="bulkAction size-s" required>
                            <option value="">-- <?= _('Bitte wählen') ?> --</option>
                            <option value="send_message"><?= _('Nachricht senden') ?></option>
                            <option value="delete"><?= _('Löschen') ?></option>
                        </select>

                    <?= Studip\Button::create(_('Ausführen'), [
                        'title' => _('Ausgewählte Aktion ausführen'),
                        'class' => 'bulkAction',
                    ]) ?>
                </td>
            </tr>
        </tfoot>
    </table>
</form>
