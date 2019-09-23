<? use Studip\Button; ?>
<form method="POST" action="<? echo URLHelper::getLink('?working_on_request=' . $reqObj->request_id); ?>">
    <?= CSRFProtection::tokenTag() ?>
    <input type="hidden" name="view" value="edit_request">
    <table class="default nohover">
        <caption>
            <?= _('Anfrage bearbeiten') ?>
        </caption>
        <colgroup>
            <col width="35%">
            <col width="65%">
        </colgroup>
        <thead>
            <tr>
                <th colspan="2">
                    <a href="<?= URLHelper::getLink($sem_link) ?>">
                        <?= $semObj->seminar_number ? htmlReady($semObj->seminar_number) . ': ' : '' ?><?= htmlReady($semObj->getName()) ?>
                    </a>
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?= _('Art der Anfrage') ?></td>
                <td><?= htmlReady($reqObj->getTypeExplained()) ?></td>
            </tr>
            <tr>
                <td><?= _('Erstellt am') ?></td>
                <td>
                    <?= strftime('%x %H:%M', $reqObj->mkdate) ?>
                    <?= _('von') ?>
                    <a href="<?= URLHelper::getLink('dispatch.php/profile', ['username' => $reqObj->user->username]) ?>">
                        <?= htmlReady($reqObj->user->getFullName()) ?>
                    </a>
                </td>
            </tr>
            <tr>
                <td><?= _('Letzte Änderung') ?></td>
                <td>
                    <?= strftime('%x %H:%M', $reqObj->chdate) ?>
                    <?= _('von') ?>
                    <a href="<?= URLHelper::getLink('dispatch.php/profile', ['username' => $modifier->username]) ?>">
                        <?= htmlReady($modifier->getFullName()) ?>
                    </a>
                </td>
            </tr>
            <tr>
                <td><?= _('Lehrende') ?></td>
                <td>
                    <? $colon = false;
                    foreach ($semObj->getMembers('dozent') as $doz)  : ?>
                        <?= $colon ? ', ' : '' ?>
                        <a href="<?= URLHelper::getLink('dispatch.php/profile', ['username' => $doz['username']]) ?>">
                            <?= htmlReady($doz['fullname']) ?>
                        </a>
                        <? $colon = true; ?>
                    <? endforeach; ?>
                </td>
            </tr>
            <tr>
                <td><?= _('verantwortliche Einrichtung') ?></td>
                <td><?= htmlReady($semObj->home_institut->name) ?>
                <? if ($semObj->institut_id != $semObj->home_institut->fakultaets_id) : ?>
                    (<?= htmlReady($semObj->home_institut->faculty->name) ?>)</td>
                <? endif ?>
            </tr>
            <tr>
                <td><?= _('aktuelle Teilnehmendenzahl') ?></td>
                <td><?= $semObj->getNumberOfParticipants('total') ?></td>
            </tr>
            <tr>
                <td style="vertical-align: top">
                    <p><strong><?= _('angeforderte Belegungszeiten') ?>:</strong></p>
                    <?
                    $dates = $semObj->getGroupedDates($reqObj->getTerminId(), $reqObj->getMetadateId());
                    if ($dates['first_event']) {
                        $i = 1;
                        if (is_array($dates['info']) && sizeof($dates['info']) > 0) {
                            foreach ($dates['info'] as $info) {
                                $name = $info['name'];
                                if ($info['weekend']) $name = '<span style="color:red">' . $info['name'] . '</span>';
                                printf("<span style=\"color: blue; font-style: italic; font-weight: bold \">%s</span>. %s<br>", $i, $name);
                                $i++;
                            }
                        }

                        if ($reqObj->getType() != 'date') {
                            echo _("regelmäßige Buchung ab") . ": " . strftime("%x", $dates['first_event']);
                        }
                    } else {
                        print _("nicht angegeben");
                    }
                    ?>
                </td>
                <td style="border-left:1px solid #1f4171;border-bottom:1px solid #1f4171; background-color: #f3f5f8; vertical-align: top"
                    rowspan="4">
                    <table style="width: 100%">
                        <tr>
                            <td style="width: 70%">
                                <strong><?= _('angeforderter Raum') ?>:</strong>
                            </td>
                            <?
                            unset($resObj);
                            $cols = 0;
                            if (is_array($_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["groups"])) foreach ($_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["groups"] as $key => $val) {
                                $cols++;
                                print "<td style=\"width: 1%\"><span style=\"color: blue; font-style: italic; font-weight: bold \">" . $cols . ".</span></td>";
                            }
                            ?>
                            <td></td>
                        </tr>
                        <tr>
                            <td>
                                <? if ($request_resource_id = $reqObj->getResourceId()) : ?>
                                    <? $resObj = ResourceObject::Factory($request_resource_id); ?>
                                    <?= $resObj->getFormattedLink($_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["first_event"]); ?>
                                    <?= tooltipicon(_('Der ausgewählte Raum bietet folgende der wünschbaren Eigenschaften:') . "\n" . $resObj->getPlainProperties(true), $resObj->getOwnerId() == 'global'); ?>
                                    <? if ($resObj->getOwnerId() == 'global')  : ?>
                                        [global]
                                    <? endif ?>
                                    <? if ($resObj->getSeats() > 1) : ?>
                                        <?= sprintf(_('- %d Plätze'), $resObj->getSeats()) ?>
                                    <? endif ?>
                                <? else : ?>
                                    <?= _('Es wurde kein Raum angefordert.'); ?>
                                <? endif ?>
                            </td>
                            <? $i = 0; ?>
                            <? if (is_array($_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["groups"]) && sizeof($_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["groups"]) > 0) : ?>
                                <? foreach ($_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["groups"] as $key => $val)  : ?>
                                    <td nowrap>
                                        <? if ($request_resource_id) : ?>
                                            <? if ($request_resource_id == $val["resource_id"]) : ?>
                                                <?= Icon::create('accept', 'accept', ['title' => _('Dieser Raum ist augenblicklich gebucht"'),
                                                                                      true])->asImg(); ?>
                                                <input style="vertical-align: top; margin: 1px 5px" type="radio"
                                                       name="selected_resource_id[<?= $i ?>]"
                                                       value="<?= $request_resource_id ?>" checked="checked">
                                            <? else : ?>
                                                <? $overlap_status = ShowToolsRequests::showGroupOverlapStatus($_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["detected_overlaps"][$request_resource_id], $val["events_count"], $val["overlap_events_count"][$request_resource_id], $val["termin_ids"]); ?>
                                                <?= $overlap_status["html"] ?>
                                                <?= sprintf('<input style="vertical-align: top; margin: 1px 5px" type="radio" name="selected_resource_id[%s]" value="%s" %s %s>', $i, $request_resource_id, ($_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["selected_resources"][$i] == $request_resource_id) ? "checked" : "", ($overlap_status["status"] == 2 || !ResourcesUserRoomsList::CheckUserResource($request_resource_id)) ? "disabled" : ""); ?>
                                            <? endif ?>
                                        <? else : ?>
                                            &nbsp;
                                        <? endif ?>
                                    </td>
                                    <? $i++; ?>
                                <? endforeach ?>

                            <? endif ?>
                            <td style="text-align: right">
                                <?
                                if (is_object($resObj)) {
                                    $seats           = $resObj->getSeats();
                                    $requested_seats = $reqObj->getSeats();
                                    if ((is_numeric($seats)) && (is_numeric($requested_seats))) {
                                        $percent_diff = (100 / $requested_seats) * $seats;
                                        if ($percent_diff > 0) $percent_diff = "+" . $percent_diff;
                                        if ($percent_diff < 0) $percent_diff = "-" . $percent_diff;
                                        printf('<small>%u %%</small>', round($percent_diff));
                                    }
                                }
                                ?>
                            </td>
                        </tr>
                        <?
                        if (Config::get()->RESOURCES_ENABLE_GROUPING) {
                            $room_group = RoomGroups::GetInstance();
                            $group_id   = $_SESSION['resources_data']['actual_room_group'];
                            ?>
                            <tr>
                                <td style="border-top:1px solid; width: 100%" colspan="<?= $cols + 2 ?>">
                                    <strong><?= _('Raumgruppe berücksichtigen') ?>:</strong>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <select name="request_tool_choose_group">
                                        <option <?= (is_null($group_id) ? 'selected' : '') ?>
                                                value="-"><?= _('Keine Raumgruppe anzeigen') ?></option>
                                        <?
                                        foreach ($room_group->getAvailableGroups() as $gid) {
                                            echo '<option value="' . $gid . '" ' . (!is_null($group_id) && $group_id == $gid ? 'selected' : '') . '>' . htmlReady(my_substr($room_group->getGroupName($gid), 0, 45)) . ' (' . $room_group->getGroupCount($gid) . ')</option>';
                                        }
                                        ?>
                                    </select>
                                </td>
                                <td colspan="<?= $cols + 2 ?>">
                                    <?= Button::create(_('Auswählen'), 'request_tool_group') ?>
                                </td>
                            </tr>
                            <?
                            if ($room_group->getGroupCount($group_id)) {
                                foreach ($room_group->getGroupContent($group_id) as $key) {
                                    ?>
                                    <tr>
                                        <td>
                                            <?
                                            $resObj = ResourceObject::Factory($key);
                                            print $resObj->getFormattedLink($_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["first_event"]);
                                            print tooltipicon(_('Der ausgewählte Raum bietet folgende der wünschbaren Eigenschaften:') . "\n" . $resObj->getPlainProperties(true), $resObj->getOwnerId() == 'global');
                                            if ($resObj->getOwnerId() == 'global') {
                                                print ' [global]';
                                            }
                                            if ($resObj->getSeats() > 1) {
                                                printf(_('- %d Plätze'), $resObj->getSeats());
                                            }
                                            ?>
                                        </td>
                                        <?
                                        $i = 0;
                                        if (is_array($_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["groups"])) {
                                            foreach ($_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["groups"] as $key2 => $val2) {
                                                print "<td width=\"1%\" nowrap>";
                                                if ($key == $val2["resource_id"]) {
                                                    print Icon::create('accept', 'accept', ['title' => _("Dieser Raum ist augenblicklich gebucht"),
                                                                                            true])->asImg();
                                                    echo '<input style="vertical-align: top; margin: 1px 5px" type="radio" name="selected_resource_id[' . $i . ']" value="' . $key . '" checked="checked">';
                                                } else {
                                                    $overlap_status = ShowToolsRequests::showGroupOverlapStatus($_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["detected_overlaps"][$key], $val2["events_count"], $val2["overlap_events_count"][$resObj->getId()], $val2["termin_ids"]);
                                                    print $overlap_status["html"];
                                                    printf("<input style=\"vertical-align: top; margin: 1px 5px\" type=\"radio\" name=\"selected_resource_id[%s]\" value=\"%s\" %s %s>", $i, $key, ($_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["selected_resources"][$i] == $key) ? "checked" : "", ($overlap_status["status"] == 2 || !ResourcesUserRoomsList::CheckUserResource($key)) ? "disabled" : "");
                                                }
                                                print "</td>";
                                                $i++;
                                            }
                                        }
                                        ?>
                                        <td style="text-align: right">
                                            <?
                                            if (is_object($resObj)) {
                                                $seats           = $resObj->getSeats();
                                                $requested_seats = $reqObj->getSeats();
                                                if ((is_numeric($seats)) && (is_numeric($requested_seats))) {
                                                    $percent_diff = (100 / $requested_seats) * $seats;
                                                    if ($percent_diff > 0) $percent_diff = "+" . $percent_diff;
                                                    if ($percent_diff < 0) $percent_diff = "-" . $percent_diff;
                                                    printf('<small>%u %%</small>', round($percent_diff));
                                                }
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <?
                                }
                            }
                        }
                        ?>
                        <tr>
                            <td style="border-top:1px solid; width: 100%" colspan="<?= $cols + 2 ?>">
                                <strong><?= _('weitere passende Räume') ?>:</strong>
                            </td>
                        </tr>
                        <?
                        if (is_array($_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["considered_resources"])) foreach ($_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["considered_resources"] as $key => $val) {
                            if ($val["type"] == "matching") $matching_rooms[$key] = true;
                            if ($val["type"] == "clipped") $clipped_rooms[$key] = true;
                            if ($val["type"] == "grouped") $grouped_rooms[$key] = true;
                        }

                        if ($matching_rooms && is_array($matching_rooms)) {
                            // filter list to [search_limit_low]...[search_limit_high]
                            $search_limit_low  = $_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["search_limit_low"];
                            $search_limit_high = $_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["search_limit_high"];
                            $matching_rooms    = array_slice($matching_rooms, $search_limit_low, $search_limit_high - $search_limit_low);
                            foreach ($matching_rooms as $key => $val) {
                                ?>
                                <tr>
                                    <td>
                                        <?
                                        $resObj = ResourceObject::Factory($key);
                                        print $resObj->getFormattedLink($_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["first_event"]);
                                        print tooltipicon(_('Der ausgewählte Raum bietet folgende der wünschbaren Eigenschaften:') . "\n" . $resObj->getPlainProperties(true), $resObj->getOwnerId() == 'global');
                                        if ($resObj->getOwnerId() == 'global') {
                                            print ' [global]';
                                        }
                                        if ($resObj->getSeats() > 1) {
                                            printf(_('- %d Plätze'), $resObj->getSeats());
                                        }
                                        ?>
                                    </td>
                                    <?
                                    $i = 0;
                                    if (is_array($_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["groups"])) {
                                        foreach ($_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["groups"] as $key2 => $val2) {
                                            print "<td nowrap>";
                                            if ($key == $val2["resource_id"]) {
                                                print Icon::create('accept', 'accept', ['title' => _('Dieser Raum ist augenblicklich gebucht'),
                                                                                        true])->asImg();
                                                echo '<input style="vertical-align: top; margin: 1px 5px" type="radio" name="selected_resource_id[' . $i . ']" value="' . $key . '" checked="checked">';
                                            } else {
                                                $overlap_status = ShowToolsRequests::showGroupOverlapStatus($_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["detected_overlaps"][$key], $val2["events_count"], $val2["overlap_events_count"][$resObj->getId()], $val2["termin_ids"]);
                                                print $overlap_status["html"];
                                                printf("<input style=\"vertical-align: top; margin: 1px 5px\" type=\"radio\" name=\"selected_resource_id[%s]\" value=\"%s\" %s %s>", $i, $key, ($_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["selected_resources"][$i] == $key) ? "checked" : "", ($overlap_status["status"] == 2 || !ResourcesUserRoomsList::CheckUserResource($key)) ? "disabled" : "");
                                            }
                                            print "</td>";
                                            $i++;
                                        }
                                    }
                                    ?>
                                    <td style="text-align: right">
                                        <?
                                        if (is_object($resObj)) {
                                            $seats           = $resObj->getSeats();
                                            $requested_seats = $reqObj->getSeats();
                                            if ((is_numeric($seats)) && (is_numeric($requested_seats))) {
                                                $percent_diff = (100 / $requested_seats) * $seats;
                                                if ($percent_diff > 0) $percent_diff = "+" . $percent_diff;
                                                if ($percent_diff < 0) $percent_diff = "-" . $percent_diff;
                                                printf('<small>%u %%</small>', round($percent_diff));
                                            }
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <?
                            }
                            ?>
                            <tr>
                                <td colspan="<?= $cols + 2 ?>" style="text-align: center">
                                    <?= _("zeige Räume") ?>
                                    <a href="<?= URLHelper::getLink('?dec_limit_low=1') ?>">-</a>
                                    <input type="text" name="search_rooms_limit_low" size="2"
                                           value="<?= ($_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["search_limit_low"] + 1) ?>">
                                    <a href="<?= URLHelper::getLink('?inc_limit_low=1') ?>">+</a>

                                    <?= _('bis') ?>
                                    <a href="<?= URLHelper::getLink('?dec_limit_high=1') ?>">-</a>
                                    <input type="text" name="search_rooms_limit_high" size="2"
                                           value="<?= $_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["search_limit_high"] ?>">
                                    <a href="<?= URLHelper::getLink('?inc_limit_high=1') ?>">+</a>

                                    <?= Icon::create('arr_2up', 'sort', ['title' => _('ausgewählten Bereich anzeigen')])->asInput(['name' => 'matching_rooms_limit_submit']) ?>
                                </td>
                            </tr>
                            <?
                        } else
                            print "<tr><td width=\"100%\" colspan=\"" . ($cols + 1) . "\">" . _("keine gefunden") . "</td></tr>";

                        //Clipped Rooms
                        if ($clipped_rooms && is_array($clipped_rooms)) {
                            ?>
                            <tr>
                                <td style="border-top:1px solid; width: 100%" colspan="<?= $cols + 2 ?>">
                                    <strong><?= _('Räume aus der Merkliste') ?>:</strong>
                                </td>
                            </tr>
                            <?
                            foreach ($clipped_rooms as $key => $val) {
                                ?>
                                <tr>
                                    <td>
                                        <?
                                        $resObj = ResourceObject::Factory($key);
                                        print $resObj->getFormattedLink($_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["first_event"]);
                                        print tooltipicon(_('Der ausgewählte Raum bietet folgende der wünschbaren Eigenschaften:') . "\n" . $resObj->getPlainProperties(true), $resObj->getOwnerId() == 'global');
                                        if ($resObj->getOwnerId() == 'global') {
                                            print ' [global]';
                                        }
                                        if ($resObj->getSeats() > 1) {
                                            printf(_('- %d Plätze'), $resObj->getSeats());
                                        }
                                        ?>
                                    </td>
                                    <?
                                    $i = 0;
                                    if (is_array($_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["groups"])) {
                                        foreach ($_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["groups"] as $key2 => $val2) {
                                            print "<td width=\"1%\" nowrap>";
                                            if ($key == $val2["resource_id"]) {
                                                print Icon::create('accept', 'clickable', ['title' => _('Dieser Raum ist augenblicklich gebucht'),
                                                                                           true])->asImg();
                                            } else {
                                                $overlap_status = ShowToolsRequests::showGroupOverlapStatus($_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["detected_overlaps"][$key], $val2["events_count"], $val2["overlap_events_count"][$resObj->getId()], $val2["termin_ids"]);
                                                print $overlap_status["html"];
                                                printf("<input style=\"vertical-align: top; margin: 1px 5px\" type=\"radio\" name=\"selected_resource_id[%s]\" value=\"%s\" %s %s>", $i, $key, ($_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["selected_resources"][$i] == $key) ? "checked" : "", ($overlap_status["status"] == 2 || !ResourcesUserRoomsList::CheckUserResource($key)) ? "disabled" : "");
                                            }
                                            print "</td>";
                                            $i++;
                                        }
                                    }
                                    ?>
                                    <td style="text-align: right">
                                        <?
                                        if (is_object($resObj)) {
                                            $seats           = $resObj->getSeats();
                                            $requested_seats = $reqObj->getSeats();
                                            if ((is_numeric($seats)) && (is_numeric($requested_seats))) {
                                                $percent_diff = (100 / $requested_seats) * $seats;
                                                if ($percent_diff > 0) $percent_diff = "+" . $percent_diff;
                                                if ($percent_diff < 0) $percent_diff = "-" . $percent_diff;
                                                printf('<small>%u %%</small>', round($percent_diff));
                                            }
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <?
                            }
                        }
                        ?>
                    </table>
                </td>
            </tr>
            <tr>
                <td valign="top">
                    <p><strong><?= _('gewünschte Raumeigenschaften') ?>:</strong></p>
                    <? $properties = $reqObj->getProperties(); ?>
                    <? if (sizeof($properties))  : ?>
                        <table cellpadding="0" cellspacing="0">
                            <colgroup>
                                <col style="width: 70%">
                                <col>
                            </colgroup>
                            <? foreach ($properties as $key => $val) : ?>
                                <tr>
                                    <td>
                                        <?= htmlReady($val['name']) ?>:
                                    </td>
                                    <td>
                                        <? if ($val['type'] == 'num' || $val['type'] == 'text') : ?>
                                            <?= htmlReady($val["state"]); ?>
                                        <? endif ?>
                                        <? if ($val['type'] == 'select') : ?>
                                            <? $options = explode(';', $val['options']); ?>
                                            <? foreach ($options as $a) : ?>
                                                <? if ($val['state'] == $a) : ?>
                                                    <?= htmlReady($a) ?>
                                                <? endif ?>
                                            <? endforeach ?>
                                        <? endif ?>
                                    </td>
                                </tr>
                            <? endforeach; ?>
                        </table>
                    <? else : ?>
                        <?= _('Es wurden keine Raumeigenschaften gewünscht.'); ?>
                    <? endif ?>
                </td>
            </tr>
            <tr>
                <td style="vertical-align: top">
                    <p><strong><?= _('Kommentar des Anfragenden') ?>:</strong></p>
                    <? if ($comment = $reqObj->getComment()) : ?>
                        <?= htmlReady($comment); ?>
                    <? else : ?>
                        <?= _('Es wurde kein Kommentar eingegeben'); ?>
                    <? endif ?>
                </td>
            </tr>
            <tr>
                <td valign="top">
                    <? $user_status_mkdate = $reqObj->getUserStatus($GLOBALS['user']->id); ?>
                    <p><strong><?= ('Benachrichtigungen') ?>:</strong></p>
                    <input type="radio" onChange="jQuery(this).closest('form').submit()" name="reply_recipients"
                           id="reply_recipients_requester" value="requester" checked>
                    <label for="reply_recipients_requester">
                        <?= _('Ersteller') ?>
                    </label>
                    <input type="radio" onChange="jQuery(this).closest('form').submit()" name="reply_recipients"
                           id="reply_recipients_lecturer"
                           value="lecturer" <?= ($reqObj->reply_recipients == 'lecturer' ? 'checked' : '') ?>>
                    <label for="reply_recipients_lecturer">
                        <?= _('Ersteller und alle Lehrenden') ?>
                    </label>
                    <br>
                    <br>
                    <p><strong><?= ('Anfrage markieren') ?>:</strong></p>
                    <input type="radio" onChange="jQuery(this).closest('form').submit()"
                           name="request_user_status"
                           id="request_user_status_0" value="0" checked>
                    <label for="request_user_status_0">
                        <?= _('unbearbeitet') ?>
                    </label>
                    <input type="radio" onChange="jQuery(this).closest('form').submit()"
                           name="request_user_status"
                           id="request_user_status_1" value="1" <?= ($user_status_mkdate ? 'checked' : '') ?>>
                    <label for="request_user_status_1">
                        <?= _('bearbeitet') ?>
                    </label>
                    <br><br>
                    <p><strong><?= _('Kommentar zur Belegung (intern)') ?>:</strong></p>
                    <textarea name="comment_internal" style="width: 90%" rows="2"></textarea>
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" valign="top" style="text-align: center">
                    <?
                    // can we dec?
                    if ($_SESSION['resources_data']["requests_working_pos"] > 0) {
                        $d = -1;
                        if ($_SESSION['resources_data']["skip_closed_requests"]) while ((!$_SESSION['resources_data']["requests_open"][$_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"] + $d]["request_id"]]) && ($_SESSION['resources_data']["requests_working_pos"] + $d > 0)) $d--;
                        if ((sizeof($_SESSION['resources_data']["requests_open"]) > 1) && (($_SESSION['resources_data']["requests_open"][$_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"] + $d]["request_id"]]) || (!$_SESSION['resources_data']["skip_closed_requests"]))) $inc_possible = true;
                    }


                    if ($inc_possible) {
                        echo Button::create('<< ' . _('Zurück'), 'dec_request');
                    }


                    echo Button::createCancel(_('Abbrechen'), 'cancel_edit_request');
                    echo Button::create(_('Löschen'), 'delete_request');

                    if (($reqObj->getResourceId() || $matching_rooms || $clipped_rooms || $grouped_rooms) && (is_array($_SESSION['resources_data']['requests_working_on'][$_SESSION['resources_data']['requests_working_pos']]['groups']) || $_SESSION['resources_data']['requests_working_on'][$_SESSION['resources_data']['requests_working_pos']]['assign_objects'])) {
                        echo Button::createAccept(_('Speichern'), 'save_state');
                        echo Button::createCancel(_('Ablehnen'), 'suppose_decline_request');
                    }

                    // can we inc?
                    if ($_SESSION['resources_data']["requests_working_pos"] < sizeof($_SESSION['resources_data']["requests_working_on"]) - 1) {
                        $i = 1;
                        if ($_SESSION['resources_data']["skip_closed_requests"]) while ((!$_SESSION['resources_data']["requests_open"][$_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"] + $i]["request_id"]]) && ($_SESSION['resources_data']["requests_working_pos"] + $i < sizeof($_SESSION['resources_data']["requests_working_on"]) - 1)) $i++;
                        if ((sizeof($_SESSION['resources_data']["requests_open"]) > 1) && (($_SESSION['resources_data']["requests_open"][$_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"] + $i]["request_id"]]) || (!$_SESSION['resources_data']["skip_closed_requests"]))) $dec_possible = true;
                    }

                    if ($dec_possible) {
                        echo Button::create(_('Weiter') . ' >>', 'inc_request');
                    }
                    ?>

                    <?
                    if (sizeof($_SESSION['resources_data']["requests_open"]) > 1) printf("<br>" . _("<b>%s</b> von <b>%s</b> Anfragen in der Bearbeitung wurden noch nicht aufgelöst."), sizeof($_SESSION['resources_data']["requests_open"]), sizeof($_SESSION['resources_data']["requests_working_on"]));
                    printf("<br>" . _("Aktueller Request: ") . "<b>%s</b>", $_SESSION['resources_data']["requests_working_pos"] + 1);
                    ?>
                </td>
            </tr>
        </tfoot>
    </table>
</form>
