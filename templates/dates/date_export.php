<?php
echo $date->toString();

if ($date->getResourceId()) :
    echo ', '. _("Ort:") .' ';
    echo implode(', ', getPlainRooms([$date->getResourceId() => '1']));
elseif ($date->getFreeRoomText()) :
    echo ', '.  _("Ort:") .' ';
    echo '(' . $date->getFreeRoomText() . ')';
endif ?>
