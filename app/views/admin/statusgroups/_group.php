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
        <? if ($tutor): ?>
        <span class="actions">
            <? $menu = ActionMenu::get() ?>
            <? $menu->addLink($controller->url_for("admin/statusgroups/editGroup/{$group->id}"),
                    _('Gruppe bearbeiten'), Icon::create('edit'), ['data-dialog' => 'size=auto']) ?>
            <? $menu->addMultiPersonSearch(
                MultiPersonSearch::get("add_statusgroup" . $group->id)
                    ->setLinkText(_('Personen hinzufügen'))
                    ->setDefaultSelectedUser($group->members->pluck('user_id'))
                    ->setExecuteURL($controller->url_for("admin/statusgroups/memberAdd/{$group->id}"))
                    ->setSearchObject($searchType)
                    ->addQuickfilter(_("aktuelle Einrichtung"), $membersOfInstitute)
                    ->addQuickfilter(_('Nicht zugeordnet'), $not_assigned)
               ) ?>
            <? $menu->addLink($controller->url_for("admin/statusgroups/deleteGroup/{$group->id}"),
                    _('Gruppe löschen'), Icon::create('trash'), ['data-dialog' => 'size=auto']) ?>
            <? $menu->addLink($controller->url_for("admin/statusgroups/sortAlphabetic/{$group->id}"),
                    _('Gruppe alphabetisch sortieren'), Icon::create('arr_2down'), ['data-dialog' => 'size=auto']) ?>
            <? if ($group->children): ?>
                <? $menu->addLink($controller->link_for("admin/statusgroups/sortGroupsAlphabetical/{$group->id}"),
                        _('Untergruppen alphabetisch sortieren'), Icon::create('filter2'),
                        ['data-confirm' => _('Sollen die Untergruppen dieser Gruppe alphabetisch sortiert werden?')]) ?>
            <? endif ?>
            <?= $menu->render() ?>
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
