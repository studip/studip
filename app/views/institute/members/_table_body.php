<tbody style="vertical-align: top;">
<? if ($th_title): ?>
    <tr>
        <th colspan="<?= 1 + count($structure) - ($structure['actions'] ? 1 : 0) ?>">
            <?= htmlReady($th_title) ?>
        </th>
    <? if ($structure['actions']): ?>
        <th class="actions">
        <?= ActionMenu::get()
            ->condition($mail_status)
            ->addLink(
                $controller->url_for('messages/write?filter=inst_status', [
                    'who'             => $key,
                    'default_subject' => Context::get()->Name,
                    'course_id'       => Context::getId(),
                ]),
                sprintf(_('Nachricht an alle Mitglieder mit dem Status %s verschicken'), $th_title),
                Icon::create('mail', 'clickable'),
                ['data-dialog' => '']
            )
            ->condition($mail_gruppe)
            ->addLink(
                $controller->url_for('messages/write', [
                    'group_id'        => $group->id,
                    'default_subject' => Context::get()->Name,
                ]),
                sprintf(_('Nachricht an alle Mitglieder der Gruppe %s verschicken'), $th_title),
                Icon::create('mail', 'clickable'),
                ['data-dialog' => '']
            ) ?>
        </th>
    <? endif; ?>
    </tr>
<? endif; ?>
<? foreach ($members as $member):
        $default_entries = DataFieldEntry::getDataFieldEntries([$member->user_id, $institute->id]);

        if ($group) {
            $role_entries = DataFieldEntry::getDataFieldEntries([$member->user_id, $group->id]);
        }
?>
    <tr>
        <td>
            <a href="<?= $controller->link_for('profile', ['username' => $member->username]) ?>">
                <?= Avatar::getAvatar($member->user_id, $member->username)->getImageTag(Avatar::SMALL) ?>
            </a>
        </td>
        <td>
        <? if ($admin_view): ?>
            <a href="<?= $controller->link_for("settings/statusgruppen#{$institute->id}", ['username' => $member->username, 'contentbox_open' => $institute->id]) ?>">
                <?= htmlReady($member->getUserFullname('full_rev')) ?>
            </a>
        <? else: ?>
            <a href="<?= $controller->link_for('profile', ['username' => $member->username]) ?>">
                <?= htmlReady($member->getUserFullname('full_rev')) ?>
            </a>
        <? endif; ?>
        </td>
    <? if ($structure['status']): ?>
        <td><?= htmlReady($member->inst_perms) ?></td>
    <? endif; ?>
    <? if ($structure['statusgruppe']): ?>
        <td></td>
    <? endif; ?>
    <? if ($structure['raum']): ?>
        <td><?= htmlReady($member->raum) ?></td>
    <? endif; ?>
    <? if ($structure['sprechzeiten']): ?>
        <td><?= htmlReady($member->sprechzeiten) ?></td>
    <? endif; ?>
    <? if ($structure['telefon']): ?>
        <td><?= htmlReady($member->Telefon) ?></td>
    <? endif; ?>
    <? if ($structure['email']): ?>
        <td><?= htmlReady(get_visible_email($member->user_id)) ?></td>
    <? endif; ?>
    <? if ($structure['homepage']): ?>
        <td><?= htmlReady($member->user_info->Home) ?></td>
    <? endif; ?>
    <? foreach (array_filter($datafields_list, function ($e) use ($structure) { return isset($structure[$e->getId()]); }) as $entry): ?>
        <td>
        <? if ($role_entries[$entry->getId()] && $role_entries[$entry->getId()]->getValue() !== 'default_value'): ?>
            <?= $role_entries[$entry->getId()]->getDisplayValue() ?>
        <? elseif ($default_entries[$entry->getId()]): ?>
            <?= $default_entries[$entry->getId()]->getDisplayValue() ?>
        <? endif; ?>
        </td>
    <? endforeach; ?>
    <? if ($structure['actions']): ?>
        <td class="actions">
        <?= ActionMenu::get()
            ->addLink(
                $controller->url_for("messages/write?rec_uname={$member->username}"),
                _('Nachricht an Benutzer verschicken'),
                Icon::create('mail', 'clickable'),
                ['data-dialog' => '']
            )
            ->conditionAll(
                $admin_view && !LockRules::Check($institute->id, 'participants') // General permission check
                && ($member->inst_perms !== 'admin' // Don't delete admins
                    || ($GLOBALS['perm']->get_profile_perm($member->user_id) === 'admin' // unless you are a global admin yourself
                        && $member->user_id !== $GLOBALS['user']->id)) // but don't delete yourself
            )
            ->condition(isset($group))
            ->addLink(
                $controller->url_for('institute/members/remove_from_group', $group->id, $type, ['username' => $member->username]),
                _('Person aus Gruppe austragen'),
                Icon::create('door-leave', 'clickable'),
                ['data-confirm' => _('Wollen Sie die Person wirklich aus der Gruppe austragen?')]
            )
            ->condition(!isset($group))
            ->addLink(
                $controller->url_for('institute/members/remove_from_institute', $type, ['username' => $member->username]),
                _('Person aus Einrichtung austragen'),
                Icon::create('door-leave', 'clickable'),
                ['data-confirm' => _('Wollen Sie die Person wirklich aus der Einrichtung austragen?')]
            ) ?>
        </td>
    <? endif; ?>
    </tr>
<? if ($structure['statusgruppe']): ?>
    <?
    $my_groups = $groups->filter(function ($group) use ($member) {
        return $group->isMember($member->user_id);
    });
    foreach ($my_groups as $group):
        $group_member = $group->members->findOneBy('user_id', $member->user_id);
    ?>
        <tr>
            <td colspan="<?= 2 + (int)!empty($structure['status']) ?>"></td>
            <td colspan="<?= 1 + count(array_filter(['raum', 'sprechzeiten', 'telefon', 'email', 'homepage'], function ($item) use ($structure) { return !empty($structure[$item]); })) ?>">
            <? if ($admin_view): ?>
                <a href="<?= $controller->link_for('admin/statusgroups/editGroup/' . $group->id) ?>">
            <? endif; ?>
                <?= htmlReady($group->getFullGenderedName($member->user_id)) ?>
            <? if ($admin_view): ?>
                </a>
            <? endif; ?>
            </td>
        <? foreach ($group_member->datafields->filter(function ($e) use ($dview) { return in_array($e->getId(), $dview); }) as $entry): ?>
            <td>
            <? if ($entry->getValue() === 'default_value'): ?>
                <?= $default_entries[$e_id]->getDisplayValue() ?>
            <? else: ?>
                <?= $entry->getDisplayValue() ?>
            <? endif; ?>
            </td>
        <? endforeach; ?>
        <? if ($structure['actions']): ?>
            <td class="actions">
            <?= ActionMenu::get()
                ->conditionAll($admin_view && !LockRules::Check($institute->id, 'participants'))
                ->addLink(
                    $controller->url_for("settings/statusgruppen#{$group->id}", [
                        'username'        => $member->username,
                        'contentbox_open' => $group->id,
                    ]),
                    _('Gruppendaten bearbeiten'),
                    Icon::create('edit', 'clickable')
                )
                ->addLink(
                    $controller->url_for('institute/members/remove_from_group', $group->id, $type, ['username' => $member->username]),
                    _('Person aus Gruppe austragen'),
                    Icon::create('door-leave', 'clickable'),
                    ['data-confirm' => _('Wollen Sie die Person wirklich aus der Gruppe austragen?')]
                ) ?>
            </td>
        <? endif; ?>
        </tr>
    <? endforeach; ?>
<? endif; ?>
<? endforeach; ?>
</tbody>
