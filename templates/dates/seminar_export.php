<?
if (!isset($show_room)) :
    // show rooms only if there is more than one
    if (empty($dates['rooms']) || sizeof($dates['rooms']) === 1) :
        $show_room = false;
    else :
        $show_room = true;
    endif;
endif;

if (!empty($dates['regular']['turnus_data']) || !empty($dates['irregular'])) :
  $output = [];
  if (is_array($dates['regular']['turnus_data'])) foreach ($dates['regular']['turnus_data'] as $cycle) :
    $first_date = sprintf(_("ab %s"), strftime('%x', $cycle['first_date']['date']));
    if ($cycle['cycle'] == 1) :
        $cycle_output = $cycle['tostring_short'] . ' (' . sprintf(_("zweiwöchentlich, %s"), $first_date) . ')';
    elseif ($cycle['cycle'] == 2) :
        $cycle_output = $cycle['tostring_short'] . ' (' . sprintf(_("dreiwöchentlich, %s"), $first_date) . ')';
    else :
      $cycle_output = $cycle['tostring_short'] . ' (' . _("wöchentlich") . ')';
    endif;
    if ($cycle['desc'])
      $cycle_output .= ' - '. $cycle['desc'];

    if ($show_room) :
        $cycle_output .= $this->render_partial('dates/_seminar_rooms',
            [
                'assigned' => $cycle['assigned_rooms'],
                'freetext' => $cycle['freetext_rooms'],
                'plain'    => true]
        );
    endif;

    $output[] = $cycle_output;
  endforeach;

  echo implode(", \n", $output);

  $freetext_rooms = [];

  if (is_array($dates['irregular'])):
    foreach ($dates['irregular'] as $date) :
        $irregular[] = $date;
        $irregular_strings[] = $date['tostring'];
        if ($date['resource_id']) :
            $irregular_rooms[$date['resource_id']]++;
        elseif ($date['raum']) :
            $freetext_rooms['('. $date['raum'] .')']++;
        endif;
    endforeach;
    unset($irregular_rooms['']);
    echo sizeof($output) ? ", \n" : '';

    $rooms = array_merge(getPlainRooms($irregular_rooms, false), array_keys($freetext_rooms));

    if (is_array($irregular) && sizeof($irregular)) :
        echo _("Termine am") . implode(', ', shrink_dates($irregular));
        if (is_array($rooms) && sizeof($rooms) > 0) :
            if (sizeof($rooms) > 3) :
                $rooms = array_slice($rooms, sizeof($rooms) - 3, sizeof($rooms));
            endif;

            if ($show_room) :
                echo ', ' . _("Ort:") . ' ';
                echo implode(', ', $rooms);
            endif;
        endif;
    endif;
  endif;
endif;
