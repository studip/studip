<?php
if (!isset($show)) $show = 3;
if (!isset($link)) $link = true;

if (is_array($dates['regular']['turnus_data'])) foreach ($dates['regular']['turnus_data'] as $cycle) :
    $pos = 0;
    if ($cycle['metadate_id'] == $cycle_id && is_array($cycle['assigned_rooms'])) foreach ($cycle['assigned_rooms'] as $resource_id => $count) :
        // get string-representation of predominant booked rooms
        if ($pos >= $show) :
            if ($show > 1)$roominfo .= ', '.sprintf(_("und %s weitere"), (sizeof($rooms)-$show));
            break;
        else :
            if ($pos > 0) $roominfo .= ', ';

            $resObj = ResourceObject::Factory($resource_id);
            if ($link) :
                $roominfo .= $resObj->getFormattedLink(TRUE, TRUE, TRUE);
            else :
                $roominfo .= htmlReady($resObj->getName());
            endif;
            unset($resObj);
        endif;

        $pos++;
    endforeach; ?>
    <?= $roominfo ?>
    <? $roominfo = '';
endforeach ?>
