<?php
# Lifter010: TODO
unset($freetext['']);
if (!isset($link)) $link = false;
if (($assigned && sizeof($assigned)) || sizeof($freetext)) :

    if ($assigned):
        $rooms = $plain ? getPlainRooms($assigned) : getFormattedRooms($assigned, $link);
    endif;

    if ($freetext):
        foreach ($freetext as $name => $count) :
            if ($name) $rooms[] = '('. ($plain ? $name : htmlReady($name)) . ')';
        endforeach;
    endif;
    $ort .= ', ' . _('Ort') .': ';
    $ort .= implode(', ', array_slice($rooms, 0, 3));
else :
    $ort = ' ' . _('k.A.');
endif;
echo $ort;
