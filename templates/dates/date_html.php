<?php
if (!isset($link)) $link = true;
echo $date->toString();
if ($date->getResourceId()) :
    echo ', '.  _("Ort:") .' ';
    echo implode(', ', getFormattedRooms([$date->getResourceId() => '1'], $link));
elseif ($date->getFreeRoomText()) :
    echo ', '.  _("Ort:") .' ';
    echo '(' . htmlReady($date->getFreeRoomText()) . ')';
endif ?>
