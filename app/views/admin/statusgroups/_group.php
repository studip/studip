<a name="group-<?= $group->id ?>"></a>
<table id="<?= $group->id ?>" class="default movable">
    <colgroup>
        <col width="1">
        <col width="1">
        <col width="10">
        <col>
        <col width="10%">
    </colgroup>
    <caption>
        <?= htmlReady($group->name) ?>
        <? if ($type['needs_size']): ?>
            <?= $group->getPlaces() ?>
        <? endif; ?>
        <? if ($tutor): ?>
        <span class="actions">
            <? if ($type['needs_self_assign']): ?>
                <? if ($group->selfassign): ?>
                    <?= Icon::create('lock-unlocked', 'inactive')->asImg() ?>
                <? else: ?>
                    <?= Icon::create('lock-locked', 'inactive')->asImg() ?>
                <? endif; ?>
            <? endif; ?>
            <a data-dialog="size=auto" title="<?= _('Gruppe ändern') ?>" href="<?= $controller->url_for("admin/statusgroups/editGroup/{$group->id}") ?>">
                <?= Icon::create('edit', 'clickable', ['title' => _('Gruppe ändern')])->asImg() ?>
            </a>
            <?= MultiPersonSearch::get("add_statusgroup" . $group->id)
                    ->setLinkText()
                    ->setDefaultSelectedUser($group->members->pluck('user_id'))
                    ->setTitle(_('Personen hinzufügen'))
                    ->setExecuteURL($controller->url_for("admin/statusgroups/memberAdd/{$group->id}"))
                    ->setSearchObject($searchType)
                    ->addQuickfilter(_("aktuelle Einrichtung"), $membersOfInstitute)
                    ->addQuickfilter(_('Nicht zugeordnet'), $not_assigned)
                    ->render() ?>
            <a data-dialog="size=auto" title="<?= _('Gruppe löschen') ?>" href="<?= $controller->url_for("admin/statusgroups/deleteGroup/{$group->id}") ?>">
                <?= Icon::create('trash', 'clickable', ['title' => _('Gruppe löschen')])->asImg() ?>
            </a>
            <a data-dialog="size=auto" title="<?= _('Gruppe alphabetisch sortieren') ?>" href="<?= $controller->url_for("admin/statusgroups/sortAlphabetic/{$group->id}") ?>">
                <?= Icon::create('arr_2down', 'clickable', ['title' => _('Gruppe alphabetisch sortieren')])->asImg() ?>
            </a>
        <? else: ?>
            <? if ($type['needs_self_assign']): ?>
                <? if ($group->isMember() && $group->selfassign): ?>
                    <a href="<?= $group->path['leave'] ?>">
                        <?= Icon::create('door-leave', 'clickable', ['title' => _('Gruppe verlassen')])->asImg() ?>
                    </a>
                <? endif; ?>
                <? if ($group->userMayJoin($user_id)): ?>
                    <a href="<?= $group->path['join'] ?>">
                        <?= Icon::create('door-enter', 'clickable', ['title' => _('Gruppe beitreten')])->asImg() ?>
                    </a>
                <? endif; ?>
            <? endif; ?>
        </span>
        <? endif; ?>
    </caption>
    <thead>
        <tr>
            <th colspan="4">
                <?= sprintf(ngettext('%u Mitglied', '%u Mitglieder', count($group->members)),
                            count($group->members)) ?>
            </th>
            <th class="actions"></th>
        </tr>
    </thead>
    <tbody>
        <?= $this->render_partial('admin/statusgroups/_members.php', ['group' => $group]) ?>
    </tbody>
</table>

<? if ($group->children): ?>
<ul class='tree-seperator'>
    <li>
    <? foreach ($group->children as $child): ?>
        <?= $this->render_partial('admin/statusgroups/_group.php', ['group' => $child]) ?>
    <? endforeach ?>
    </li>
</ul>
<? endif; ?>
