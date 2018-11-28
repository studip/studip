<date><?
echo $date->toString();
if ($date->getResourceId()) :
    echo ', '. _("Ort:") .' ';
    echo htmlReady(implode(', ', getPlainRooms(array($date->getResourceId() => '1'))));
endif ?></date>
