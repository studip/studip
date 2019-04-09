<date><?
echo $date->toString();
if ($date->getResourceId()) :
    echo ', '. _("Ort:") .' ';
    echo htmlReady(implode(', ', getPlainRooms([$date->getResourceId() => '1'])));
endif ?></date>
