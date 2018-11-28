<?
if (!isset($link)) $link = true;

// condense regular dates by room
if (is_array($dates['regular']['turnus_data'])) foreach ($dates['regular']['turnus_data'] as $cycle) :
    $first_date   = sprintf(_("ab %s"), strftime('%x', $cycle['first_date']['date']));
    $cycle_output = $cycle['tostring'] . ' (' . $first_date . ')';
    if ($cycle['desc'])
        $cycle_output .= ', <i>' . htmlReady($cycle['desc']) . '</i>';

    if ($show_room) :
        $cycle_output .= $this->render_partial('dates/_seminar_rooms', ['assigned' => $cycle['assigned_rooms'],
                                                                        'freetext' => $cycle['freetext_rooms'],
                                                                        'link'     => $link
        ]);
    endif;

    if (is_array($cycle['assigned_rooms'])) foreach ($cycle['assigned_rooms'] as $room_id => $count) :
        $resObj = ResourceObject::Factory($room_id);
        if ($link) {
            $output[$resObj->getFormattedLink(true, true, true)][] = $cycle_output .' ('. $count .'x)';
        } else {
            $output[htmlReady($resObj->getName())][] = $cycle_output .' ('. $count .'x)';
        }
    endforeach;
    if (is_array($cycle['freetext_rooms'])) foreach ($cycle['freetext_rooms'] as $room => $count) :
        if ($room) :
            $output['(' . htmlReady($room) . ')'][] = $cycle['tostring'] . ' (' . $count . 'x)';
        elseif ($cycle['tostring']) :
            $without_location = $cycle['tostring'];
            if($count) :
                $without_location .=  '(' . $count . 'x)';
            endif;
            $output[_('k.A.')][] = $without_location;
        endif;
    endforeach;
endforeach;


// condense irregular dates by room
if (is_array($dates['irregular'])) foreach ($dates['irregular'] as $date) :
    if (isset($date['resource_id'])) :
        $output_dates[$date['resource_id']][] = $date;
    elseif (!empty($date['raum'])) :
        $output_dates[$date['raum']][] = $date;
    else :
        $output_dates[_('k.A.')][]  = $date['tostring'];
    endif;
endforeach;

// now shrink the dates for each room/freetext and add them to the output
if (is_array($output_dates)) foreach ($output_dates as $dates) :
    if (isset($dates[0]['resource_id'])) :
        $resObj = ResourceObject::Factory($dates[0]['resource_id']);
        if ($link) {
            $output[$resObj->getFormattedLink(true, true, true)][] = implode('<br>', shrink_dates($dates));
        } else {
            $output[htmlReady($resObj->getName())][] = implode('<br>', shrink_dates($dates));
        }
    elseif (isset($dates[0]['raum'])) :
        $output['(' . htmlReady($dates[0]['raum']) . ')'][] = implode('<br>', shrink_dates($dates));
    else :
        $output[_('k.A.')][] = implode('<br>', $dates);
    endif;
endforeach;
?>


<? if (!is_array($output) || count($output) === 0) : ?>
    <?= htmlReady($ort) ?: _("nicht angegeben") ?>
<? else: ?>
    <table class="default">
        <? foreach ($output as $room => $dates) : ?>
        <tr>
            <td style="vertical-align: top"><?= $room ?></td>
            <td>
                <? $dates = implode('<br>', $dates) ?>

                <? if (mb_strlen($dates) > 222 && !$disable_list_shrinking) : ?>
                    <?= mb_substr($dates, 0, 228) ?>
                    <div class="more-location-dates-infos" style="display:none">
                        <?= $dates ?>
                    </div>
                    <div>
                        <span class='more-location-digits'>...</span>
                        <a class="more-location-dates" style="cursor: pointer; margin-left: 3px">(mehr)</a>
                    </div>
                <? else : ?>
                    <?= $dates ?>
                <? endif ?>
            </td>
            <? endforeach ?>
    </table>
<? endif ?>
