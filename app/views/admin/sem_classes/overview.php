<?php

/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */
?>
<table class="default">
    <thead>
        <tr>
            <th><?= _("ID") ?></th>
            <th><?= _("Veranstaltungskategorie") ?></th>
            <th><?= _("Anzahl Veranstaltungstypen") ?></th>
            <th><?= _("Anzahl Veranstaltungen") ?></th>
            <th><?= _("Zuletzt geändert") ?></th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <? foreach ($GLOBALS['SEM_CLASS'] as $id => $sem_class) : ?>
        <tr>
            <td class="id"><?= htmlReady($id) ?></td>
            <td><?= htmlReady($sem_class['name']) ?></td>
            <td><?= count($sem_class->getSemTypes()) ?></td>
            <td><?= $sem_class->countSeminars() ?></td>
            <td><?= date("j.n.Y H:i", $sem_class['chdate']) ?> <?= _("Uhr") ?></td>
            <td class="actions">
                <a href="<?= URLHelper::getLink("dispatch.php/admin/sem_classes/details", ['id' => $id]) ?>" title="<?= _("Editieren dieser Veranstaltungskategorie") ?>">
                <?= Icon::create('edit', 'clickable')->asImg(['class' => "text-bottom"]) ?>
                </a>
            </td>
        </tr>
    <? endforeach ?>
    </tbody>
</table>

<?
$sidebar = Sidebar::Get();
$sidebar->setTitle(PageLayout::getTitle());
$sidebar->setImage('sidebar/plugin-sidebar.png');
$links = new ActionsWidget();
$links->addLink(
    _('Neue Kategorie anlegen'),
    $controller->url_for('admin/sem_classes/add_sem_type'),
    Icon::create('add', 'clickable'),
    [
        'onClick'     => 'STUDIP.sem_classes.add(); return false;',
        'data-dialog' => 'size=auto'
    ]
);
$sidebar->addWidget($links);
