<?php
if (!$dates['regular']['turnus_data'] && empty($dates['irregular'])) {
    if ($dates['ort'] && $show_room) {
        echo htmlReady($dates['ort']);
    } else {
        echo _('Die Zeiten der Veranstaltung stehen nicht fest.');
    }
} else {
    if (!isset($link)) {
        $link = true;
    }
    if (!isset($show_room)) {
        // show rooms only if there is more than one
        $show_room = count($dates['rooms']) > 1;
    }

    $output = [];

    if (is_array($dates['regular']['turnus_data'])) {
        foreach ($dates['regular']['turnus_data'] as $cycle) {
            $first_date = sprintf(
                _('ab %s'),
                strftime('%x', $cycle['first_date']['date'])
            );
            $cycle_output = $cycle['tostring'] . ' (' . $first_date . ')';
            if ($cycle['desc']) {
                $cycle_output .= ', <em>' . htmlReady($cycle['desc']) . '</em>';
            }

            if ($show_room) {
                $cycle_output .= $this->render_partial('dates/_seminar_rooms', [
                    'assigned' => $cycle['assigned_rooms'],
                    'freetext' => $cycle['freetext_rooms'],
                    'link' => $link
                ]);
            }

            $output[] = $cycle_output;
        }
    }

    echo implode('<br>', $output);
    echo $output ? '<br>' : '';

    $freetext_rooms = [];

    if (is_array($dates['irregular'])) {
        foreach ($dates['irregular'] as $date) {
            $irregular[] = $date;
            $irregular_strings[] = $date['tostring'];
            if ($date['resource_id']) {
                $irregular_rooms[$date['resource_id']]++;
            } elseif ($date['raum']) {
                $freetext_rooms['(' . htmlReady($date['raum']) . ')']++;
            }
        }
        unset($irregular_rooms['']);

        if (is_array($irregular) && sizeof($irregular)) {
            $dates = shrink_dates($irregular);

            echo _("Termine am");
            if (is_array($dates)) {
                if (count($dates) > 10) {
                    echo implode(', ', array_slice($dates, 0, 10));

                    echo '<span class="more-dates-infos" style="display: none">';
                    echo ', ';
                    echo implode(', ', array_slice($dates, 10));
                    echo '</span>';
                    echo '<span class="more-dates-digits"> ...</span>';
                    echo '<a class="more-dates" style="cursor: pointer; margin-left: 3px"
                 title="' . _('Blenden Sie die restlichen Termine ein') . '">(' ._('mehr'). ')</a>';
                } else {
                    $string = implode(', ', $dates);
                    if (mb_strlen($string) > 222) {
                        echo mb_substr($string,0, 128);
                        echo '<span class="more-dates-infos" style="display: none">';
                        echo mb_substr($string, -1, 1) != ','? ', ' : ' ';
                        echo mb_substr($string, 128);
                        echo '</span>';
                        echo '<span class="more-dates-digits"> ...</span>';
                        echo '<a class="more-dates" style="cursor: pointer; margin-left: 3px"
                            title="' . _('Blenden Sie die restlichen Termine ein') . '">(' ._('mehr'). ')</a>';
                    } else {
                        echo $string;
                    }
                }
            }

            if ($show_room) {
                echo $this->render_partial('dates/_seminar_rooms', array(
                    'assigned'   => $irregular_rooms,
                    'freetext'   => $freetext_rooms,
                    'link'       => $link,
                    'prefix'     => count($dates) > 10 ? '<br>' : ', ',
                    'hide_empty' => true,
                ));
            }
        }
    }

    if ($link_to_dates) {
        echo '<br>';
        printf(
            _('Details zu allen Terminen im %sAblaufplan%s'),
            '<a href="' . URLHelper::getLink('seminar_main.php', array('auswahl' => $seminar_id, 'redirect_to' => 'dispatch.php/course/dates')) . '">',
            '</a>'
        );
    }
}
