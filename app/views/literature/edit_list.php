<? if (!$lists) : ?>
    <?= _('Sie haben noch keine Listen angelegt.') ?><br>
    <br>
<? else : ?>
    <?= Icon::create('visibility-visible', Icon::ROLE_INFO) ?>
    <?= sprintf(
        _('%s öffentlich sichtbare Listen, insgesamt %s Einträge'),
        $list_count['visible'],
        $list_count['visible_entries']
    ) ?>
    <br>

    <?= Icon::create('visibility-invisible', Icon::ROLE_INFO) ?>
    <?= sprintf(
        _('%s unsichtbare Listen, insgesamt %s Einträge'),
        $list_count['invisible'],
        $list_count['invisible_entries']
    ) ?>
    <br>
    <br>
<? endif ?>
<? $treeview->showTree() ?>

<?php
$sidebar = Sidebar::get();
$sidebar->setImage('sidebar/literature-sidebar.png');
$widget = $sidebar->addWidget(new ActionsWidget());

$widget->addLink(
    _('Neue Literaturliste'),
    $controller->url_for('literature/edit_list#anchor', ['cmd' => 'NewItem', 'item_id' => 'root', 'foo' => DbView::get_uniqid()]),
    Icon::create('add'),
    tooltip2(_('Eine neue Literaturliste anlegen'))
);
$widget->addLink(
    _('Neue Literatur anlegen'),
    $controller->url_for('literature/edit_element', ['_range_id' => 'new_entry', 'return_range' => $_range_id]),
    Icon::create('literature+add')
)->asDialog();
$widget->addLink(
    _('Literatur importieren'),
    $controller->url_for('literature/import_list', ['return_range' => $_range_id]),
    Icon::create('literature+add')
)->asDialog();

ob_start();
?>
<?=$clip_form->getFormStart(URLHelper::getLink($treeview->getSelf())); ?>
<?=$clip_form->getFormField("clip_content", array_merge(['size' => $clipboard->getNumElements()],(array) $attributes['lit_select']))?>
<?=$clip_form->getFormField("clip_cmd", $attributes['lit_select'])?>
<div align="center">
<?=$clip_form->getFormButton("clip_ok",['style'=>'vertical-align:middle;margin:3px;'])?>
</div>
<?= $clip_form->getFormEnd(); ?>
<?
$content = ob_get_clean();

$widget = $sidebar->addWidget(new SidebarWidget());
$widget->setTitle(_('Merkliste'));
$widget->addElement(new WidgetElement($content));

$widget = $sidebar->addWidget(new ExportWidget());
$widget->addLink(
    _('Druckansicht'),
    $controller->url_for('literature/print_view', compact('_range_id')),
    Icon::create('print'),
    ['target' => '_blank']
);
