<?php
if ($list){
    echo $list;
} else {
    echo _("Es wurde noch keine Literatur erfasst");
}
?>
<?php

if ($list){
    $sidebar = Sidebar::get();
    $widget = new ExportWidget();
    $widget->addLink(_('Druckansicht'), URLHelper::getURL('dispatch.php/literature/print_view?_range_id='.$_range_id), Icon::create('print', 'info'), ['target' => '_blank']);
    $sidebar->addWidget($widget);
}
