<?php
if ($list){
    echo $list;
} else {
    echo _("Es wurde noch keine Literatur erfasst");
}
?>
<?php

$sidebar = Sidebar::get();
$sidebar->setImage('sidebar/literature-sidebar.png');
if ($list){
    $widget = new ExportWidget();
    $widget->addLink(_('Druckansicht'), URLHelper::getLink('dispatch.php/literature/print_view?_range_id='.$_range_id), Icon::create('print', 'info'), array('target' => '_blank'));
    $sidebar->addWidget($widget);
}
