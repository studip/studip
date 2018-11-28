<? if (! $lists) : ?>
    <?= _('Sie haben noch keine Listen angelegt.') ?><br>
    <br>
<? else : ?>
    <?=Icon::create('visibility-visible', 'info')->asImg();?>&nbsp;
    <?=sprintf(_("%s öffentlich sichtbare Listen, insgesamt %s Einträge"),$list_count['visible'],$list_count['visible_entries']).'<br>'?>
    <?=Icon::create('visibility-invisible', 'info')->asImg()?>&nbsp;
    <?=sprintf(_("%s unsichtbare Listen, insgesamt %s Einträge"),$list_count['invisible'],$list_count['invisible_entries']).'<br>'?>
    <br>
<? endif ?>
<? $treeview->showTree(); ?>
<?php
$sidebar = Sidebar::get();
$sidebar->setImage('sidebar/literature-sidebar.png');
$widget = new ActionsWidget();
$widget->addLink(_('Literatur importieren'), URLHelper::getLink('dispatch.php/literature/import_list?return_range='.$_range_id), Icon::create('literature+add', 'clickable'), array('data-dialog' => ''));
$widget->addLink(_('Neue Literatur anlegen'), URLHelper::getLink('dispatch.php/literature/edit_element?_range_id=new_entry&return_range='.$_range_id), Icon::create('literature+add', 'clickable'), array('data-dialog' => ''));
$sidebar->addWidget($widget);
ob_start();
?>
<?=$clip_form->getFormStart(URLHelper::getLink($treeview->getSelf())); ?>
<?=$clip_form->getFormField("clip_content", array_merge(array('size' => $clipboard->getNumElements()),(array) $attributes['lit_select']))?>
<?=$clip_form->getFormField("clip_cmd", $attributes['lit_select'])?>
<div align="center">
<?=$clip_form->getFormButton("clip_ok",array('style'=>'vertical-align:middle;margin:3px;'))?>
</div>
<?= $clip_form->getFormEnd(); ?>
<?
$content = ob_get_clean();
$widget = new SidebarWidget();
$widget->setTitle(_('Merkliste'));
$widget->addElement(new WidgetElement($content));
$sidebar->addWidget($widget);
$widget = new ExportWidget();
$widget->addLink(_('Druckansicht'), URLHelper::getLink('dispatch.php/literature/print_view?_range_id='.$_range_id), Icon::create('print', 'clickable'), array('target' => '_blank'));
$sidebar->addWidget($widget);