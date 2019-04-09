<?
# Lifter010: TODO
use Studip\Button,
    Studip\LinkButton;
?>
<form name="Formular" method="post" action="<?= URLHelper::getLink('?change_object_schedules='. (!$resAssign->isNew() ?  $resAssign->getId() : 'NEW')); ?>#anker">
<?= CSRFProtection::tokenTag() ?>
<input type="hidden" name="quick_view" value="<?=$used_view ?>">
<input type="hidden" name="quick_view_mode" value="<?=$view_mode ?>">
<input type="hidden" name="change_schedule_resource_id" value="<? printf ("%s", (!$resAssign->isNew()) ? $resAssign->getResourceId() : $_SESSION['resources_data']["actual_object"]); ?>">
<input type="hidden" name="change_schedule_repeat_month_of_year" value="<? echo $resAssign->getRepeatMonthOfYear() ?>">
<input type="hidden" name="change_schedule_repeat_day_of_month" value="<? echo $resAssign->getRepeatDayOfMonth() ?>">
<input type="hidden" name="change_schedule_repeat_week_of_month" value="<? echo $resAssign->getRepeatWeekOfMonth() ?>">
<input type="hidden" name="change_schedule_repeat_day_of_week" value="<? echo $resAssign->getRepeatDayOfWeek() ?>">
<input type="hidden" name="change_schedule_repeat_interval" value="<? echo $resAssign->getRepeatInterval() ?>">
<input type="hidden" name="change_schedule_repeat_quantity" value="<? echo $resAssign->getRepeatQuantity() ?>">

<table class="default nohover">
    <tbody>
        <tr>
            <td colspan="3" align="center">
            <? if (!empty($_SESSION['assign_object_has_holidays'][$resAssign->id])) : ?>
                <?  $holidays = "<br>" . htmlReady(implode(', ', $_SESSION['assign_object_has_holidays'][$resAssign->id])) . "<br>";
                    if (count($_SESSION['assign_object_has_holidays'][$resAssign->id]) > 1) {
                        $msg = sprintf(_("Die Belegung überschneidet sich mit folgenden Feiertagen: %s Wollen Sie die Belegung dennoch speichern?"), $holidays);
                    } else {
                        $msg = sprintf(_("Die Belegung überschneidet sich mit folgendem Feiertag: %s Wollen Sie die Belegung dennoch speichern?"), $holidays);
                    }
                ?>
                <?= MessageBox::error($msg . "<br>". Button::createAccept(_('JA!'), 'ignore_holidays') . ' ' . Button::createCancel(_('NEIN!'), 'no_ignore_holidays')) ?>
            <? endif; ?>

            <? if ($resAssign->isNew()) : ?>
                <?= MessageBox::info(_("Sie erstellen eine neue Belegung")) ?>
            <? endif; ?>

            <? if (!$lockedAssign) : ?>
                <?= Button::createAccept(_('Übernehmen'), 'submit') ?>
                <?= LinkButton::CreateCancel(_('Abbrechen'), URLHelper::getURL('?cancel_edit_assign=1&quick_view_mode='. $view_mode)) ?>
            <? endif; ?>

            <? if ($killButton) : ?>
                <?= Button::create(_('Löschen'), 'kill_assign') ?>
            <? endif; ?>

            <? if ($lockedAssign) : ?>
                <br>
                <?= Icon::create('info-circle', 'inactive')->asImg() ?>
                <? if ($owner_type == "sem") : ?>
                    <?= sprintf ( _("Diese Belegung ist ein regelmäßiger Termin der Veranstaltung %s, die in diesem Raum stattfindet."),
                        ($perm->have_studip_perm("user", $seminarID)) ?
                            "<a href=\"seminar_main.php?auswahl=". $seminarID ."\" onClick=\"return check_opener(this)\">". htmlReady($seminarName) ."</a>" :
                            "<a href=\"dispatch.php/course/details/?&sem_id=". $seminarID ."\" onClick=\"return check_opener(this)\">". htmlReady($seminarName) ."</a>");
                    ?>
                    <? if ($perm->have_studip_perm("tutor", $seminarID)) : ?>
                        <br>
                        <?= sprintf(_("Um die Belegung zu verändern, ändern Sie diese auf der Seite %sZeiten / Räume%s der Veranstaltung"),
                                    Icon::create('schedule', 'info')->asImg() . "&nbsp;" .
                                    "<a href=" . URLHelper::getURL("dispatch.php/course/timesrooms", ['cid' => $seminarID]) . "onClick=\"return check_opener(this)\">",
                                    "</a>");
                        ?>
                    <? endif; ?>
                <? elseif ($owner_type == "date") : ?>
                    <?= sprintf (_("Diese Belegung ist ein Einzeltermin der Veranstaltung %s, die in diesem Raum stattfindet."),
                        ($perm->have_studip_perm("user", $seminarID)) ?
                            "<a href=\"seminar_main.php?auswahl=" . $seminarID . "\" onClick=\"return check_opener(this)\">". htmlReady($seminarName) ."</a>" :
                            "<a href=\"dispatch.php/course/details/?&sem_id=". $seminarID ."\" onClick=\"return check_opener(this)\">". htmlReady($seminarName) ."</a>");
                        ?>
                    <? if ($perm->have_studip_perm("tutor", $seminarID)) : ?>
                        <br>
                        <?= sprintf(_("Um die Belegung zu verändern, ändern Sie bitte den Termin auf der Seite %sZeiten / Räume%s der Veranstaltung"),
                                   Icon::create('schedule', 'info')->asImg() . "&nbsp;" .
                                   "<a href=" . URLHelper::getURL("dispatch.php/course/timesrooms", ['cid' => $seminarID]) ." onClick=\"return check_opener(this)\">",
                                   "</a>");
                        ?>
                    <? endif ?>
                <? else : ?>
                    <?= _("Sie haben nicht die Berechtigung, diese Belegung zu bearbeiten."); ?>
                <? endif; ?>
            <? endif; ?>
            </td>
        </tr>
        <tr>
            <td valign="top">
                <?=_("Datum/erster Termin:")?><br>
            <? if ($lockedAssign) : ?>
                <strong><?=strftime("%A, %x",$resAssign->getBegin()) ?></strong>
            <? else : ?>
                <input name="changeTime" data-date-picker size="8" value="<?=strftime('%x',$resAssign->getBegin())?>">
            <? endif; ?>
            </td>

            <td width="40%">
                <?=_("Art der Wiederholung:")?><br>
            <? if ($lockedAssign) :
                if ($resAssign->getRepeatMode()=="w") :
                    if ($resAssign->getRepeatInterval() == 2)
                        echo "<b>"._("zweiwöchentlich")."</b>";
                    else
                        echo "<b>"._("wöchentlich")."</b>";
                else :
                    if (($owner_type == "date") && (isMetadateCorrespondingDate($resAssign->getAssignUserId())))
                        echo "<b>"._("Einzeltermin zu regelmäßigen Veranstaltungszeiten")."</b>";
                    else
                        echo "<b>"._("keine Wiederholung (Einzeltermin)")."</b>";
                endif;
                ?>
            <? else : ?>
                <? $repeat_buttons = [
                    'na' => ['name' => _('Keine'), 'action' => 'change_schedule_repeat_none'],
                    'd'  => ['name' => _('Täglich'), 'action' => 'change_schedule_repeat_day'],
                    'w'  => ['name' => ucfirst(_('wöchentlich')), 'action' => 'change_schedule_repeat_week'],
                    'sd' => ['name' => _('Mehrtägig'), 'action' => 'change_schedule_repeat_severaldays'],
                    'm'  => ['name' => _('Monatlich'), 'action' => 'change_schedule_repeat_month'],
                    'y'  => ['name' => _('Jährlich'),  'action' => 'change_schedule_repeat_year']
                ] ?>

                <? foreach ($repeat_buttons as $repeat_mode => $button) : ?>
                    <? if ($resAssign->getRepeatMode() == $repeat_mode) : ?>
                        <?= Button::createAccept($button['name'], $button['action']) ?>
                    <? else : ?>
                        <?= Button::create($button['name'], $button['action']) ?>
                    <? endif ?>
                    <?= ($repeat_mode == 'w') ? '<br>' : '' ?>
                <? endforeach ?>
            <? endif; ?>
            </td>
        </tr>

        <tr>
            <td valign="top">
                <?=_("Beginn/Ende:")?><br>
            <?
            if ($lockedAssign) :
                echo "<b>".date("H:i",$resAssign->getBegin())." - ".date("H:i",$resAssign->getEnd())." </b>";
            else : ?>
                <input name="change_schedule_start_hour" value="<? echo date("G",$resAssign->getBegin()); ?>" size=2 maxlength="2">
                :<input name="change_schedule_start_minute" value="<? echo date("i",$resAssign->getBegin()); ?>" size=2 maxlength="2"><?=_("Uhr")?>
                &nbsp; &nbsp; <input name="change_schedule_end_hour"  value="<? echo date("G",$resAssign->getEnd()); ?>" size=2 maxlength="2">
                :<input name="change_schedule_end_minute" value="<? echo date("i",$resAssign->getEnd()); ?>" size=2 maxlength="2"><?=_("Uhr")?>
            <? endif; ?>
            </td>
            <td width="40%" valign="top">
            <? if ($resAssign->getRepeatMode() != "na") : ?>
                <?if ($resAssign->getRepeatMode() != "sd") print _("Wiederholung bis spätestens:"); else print _("Letzter Termin:"); ?><br>
            <?
            if ($lockedAssign) :
                echo "<b>".date("d.m.Y",$resAssign->getRepeatEnd())."</b>";
            else :
            ?>
                <input name="changeRepeatTime" id="changeRepeatTime" value="<?if(mb_strlen($resAssign->getRepeatEnd())> 0) :?><?=date('j.n.Y',$resAssign->getRepeatEnd())?><?endif;?>">
                <? if (($resAssign->getRepeatMode() != "y") && ($resAssign->getRepeatMode() != "sd")) : ?>
                    <input type="CHECKBOX" <? printf ("%s", ($resAssign->isRepeatEndSemEnd()) ? "checked" : "") ?> name="change_schedule_repeat_sem_end"> <?=_("Ende der Vorlesungszeit")?>
                <? endif;
            endif;
            ?>
            <?  else : ?> &nbsp;
            <? endif; ?>
            </td>
        </tr>

        <tr>
            <td valign="top">
                <?=_("eingetragen für die Belegung:")?><br>
                <?
                $user_name=$resAssign->getUsername(FALSE);
                if ($user_name)
                    echo "<b>". htmlReady($user_name) . "</b>";
                else
                    echo "<b>-- "._("keinE Stud.IP NutzerIn eingetragen")." -- &nbsp;</b>";
                if (!$lockedAssign) : ?>
                <br><br>
                    <? if ($user_name)
                        print _("einen anderen Benutzer (NutzerIn oder Einrichtung) eintragen:");
                     else
                        print _("einen Nutzer (Person oder Einrichtung) eintragen:"); ?>
                    <br>
                    <? showSearchForm("search_user", $search_string_search_user, FALSE, TRUE, FALSE, FALSE, FALSE, "up") ?> <br>
                    <?=_("freie Eingabe zur Belegung:")?><br>
                    <input name="change_schedule_user_free_name" value="<?= htmlReady($resAssign->getUserFreeName()); ?>" size=40 maxlength="255">
                    <br><?=_("<b>Beachten Sie:</b> Wenn Sie einen NutzerIn oder eine Einrichtung eintragen, kann diese NutzerIn oder berechtigte Personen die Belegung selbstständig aufheben. Sie können die Belegung aber auch frei eingeben.")?>
                    <input type ="hidden" name="change_schedule_assign_user_id" value="<? echo $resAssign->getAssignUserId(); ?>">
                    <input type ="hidden" name="change_schedule_repeat_mode" value="<? echo $resAssign->getRepeatMode(); ?>">
                <? endif; ?>
            </td>
            <td valign="top">
            <? if (($resAssign->getRepeatMode() != "na") && ($resAssign->getRepeatMode() != "sd") && ($owner_type != "sem") && ($owner_type != "date")) :?>
                <?=_("Wiederholungsturnus:")?><br>
                <?  if (!$lockedAssign) : ?>
                <select name="change_schedule_repeat_interval" value="<? echo $resAssign->getRepeatInterval(); ?>" size="2" maxlength="2">
                <? endif;
                switch ($resAssign->getRepeatMode()) :
                    case "d":
                        $str[1]= _("jeden Tag");
                        $str[2]= _("jeden zweiten Tag");
                        $str[3]= _("jeden dritten Tag");
                        $str[4]= _("jeden vierten Tag");
                        $str[5]= _("jeden fünften Tag");
                        $str[6]= _("jeden sechsten Tag");
                        $max=6;
                    break;
                    case "w":
                        $str[1]= _("jede Woche");
                        $str[2]= _("jede zweite Woche");
                        $str[3]= _("jede dritte Woche");
                        $str[4]= _("jede vierte Woche");
                        $max=4;
                    break;
                    case "m":
                        $str[1]= _("jeden Monat");
                        $str[2]= _("jeden zweiten Monat");
                        $str[3]= _("jeden dritten Monat");
                        $str[4]= _("jeden vierten Monat");
                        $str[5]= _("jeden fünften Monat");
                        $str[6]= _("jeden sechsten Monat");
                        $str[7]= _("jeden siebten Monat");
                        $str[8]= _("jeden achten Monat");
                        $str[9]= _("jeden neunten Monat");
                        $str[10]= _("jeden zehnten Monat");
                        $str[11]= _("jeden elften Monat");
                        $max=11;
                    break;
                    case "y":
                        $str[1]= _("jedes Jahr");
                        $str[2]= _("jedes zweite Jahr");
                        $str[3]= _("jedes dritte Jahr");
                        $str[4]= _("jedes vierte Jahr");
                        $str[5]= _("jedes fünfte Jahr");
                        $max=5;
                    break;
                endswitch;

                if (!$lockedAssign) :
                    for ($i=1; $i<=$max; $i++) :
                        if ($resAssign->getRepeatInterval() == $i)
                            printf ("<option value=\"%s\" selected>%s</option>", $i, $str[$i]);
                        else
                            printf ("<option value=\"%s\">%s</option>", $i, $str[$i]);
                    endfor;
                    print "</select>";
                else :
                    print "<b>".$str[$resAssign->getRepeatInterval()]."</b>";
                endif; ?>
                <br>
                <?=_("begrenzte Anzahl der Wiederholungen:")?><br>
                <?
                if (!$lockedAssign) :
                    printf (_("max. %s Mal wiederholen"), "&nbsp;<input name=\"change_schedule_repeat_quantity\" value=\"".(($resAssign->getRepeatQuantity() != -1) ? $resAssign->getRepeatQuantity() : "")."\" size=\"4\" maxlength=\"4\">&nbsp;");
                    if ($resAssign->getRepeatQuantity() == -1): ?>
                        <input type="hidden" name="change_schedule_repeat_quantity_infinity" value="TRUE">
                    <? endif; ?>
                <? elseif ($resAssign->getRepeatQuantity() != -1) :
                    printf ("<b>"._("max. %s Mal wiederholen")." </b>",$resAssign->getRepeatQuantity());
                else :
                    print ("<b>"._("unbegrenzt")."</b>");
                endif;
            else : ?>
            &nbsp;
            <? endif; ?>
            </td>
        </tr>

        <? if ($ObjectPerms->havePerm('admin')) : ?>
        <tr>
            <td align="left">
                <?= _("Kommentar (intern)") ?>:<br>
                <textarea name="comment_internal" rows="4" style="width: 98%;"><?= $resAssign->getCommentInternal() ?></textarea>
                <input type="hidden" name="change_comment_internal" value="1">
            </td>
            <td></td>
        </tr>
        <? endif ?>

        <? if ($ObjectPerms->havePerm('admin') || !$lockedAssign) : ?>
        <tr>
            <td colspan="3" align="center"><br>&nbsp;
                <?= Button::createAccept(_('Übernehmen'), 'submit') ?>
                <?= LinkButton::CreateCancel(_('Abbrechen'), URLHelper::getURL('?cancel_edit_assign=1&quick_view_mode='. $view_mode)) ?>
                <? if ($killButton) : ?>
                    <?= Button::create(_('Löschen'), 'kill_assign') ?>
                <? endif; ?>
                <br>
            </td>
        </tr>
        <?  endif; ?>

        <? if (($ResourceObjectPerms->havePerm("tutor")) && (!$resAssign->isNew())) : ?>
        <tr>
            <td class="blank" colspan="3" width="100%">&nbsp;
            <?= Request::submitted('search_room') ? '<a name="anker"> </a>' : '' ?>
            </td>
        </tr>

        <tr>
            <td colspan="2">
                <b><?=_("weitere Aktionen:")?></b>
            </td>
        </tr>

        <tr>
            <td valign="top">
                <?
            if ($owner_type == "sem" || $owner_type == "date") {
                ?>
                <b><?=_("Belegung in anderen Raum verschieben:")?></b><br>
                <?=_("Sie können diese Belegung in einen anderen Raum verschieben. <br>Alle anderen Angaben bleiben unverändert.");?>
                <br>&nbsp;
                <?
            } else {
                ?>
                <table cellspacing="5" cellpadding="2" border="0">
                <tr>
                    <td>
                    <input <?=($change_schedule_move_or_copy != 'copy' ? 'checked' : '')?> type="radio" name="change_schedule_move_or_copy" id="change_schedule_move_or_copy1" value="move" <?= Request::submitted('search_room') ? 'readonly="readonly"' : '' ?>>
                    </td>
                    <td>
                    <label for="change_schedule_move_or_copy1" style="font-weight:bold;">
                    <?=_("Belegung in anderen Raum verschieben")?>
                    </label>
                    </td>
                    </td>
                </tr>
                <tr>
                    <td>
                    <input <?=($change_schedule_move_or_copy == 'copy' ? 'checked' : '')?> type="radio" name="change_schedule_move_or_copy" id="change_schedule_move_or_copy2" value="copy" <?= Request::submitted('search_room') ? 'readonly="readonly"' : '' ?>>
                    </td>
                    <td>
                    <label for="change_schedule_move_or_copy2" style="font-weight:bold;">
                    <?=_("Belegung in andere Räume kopieren")?>
                    </label>
                    </td>
                    </td>
                </tr>
                </table>
                <?
            }
            $result = null;
            if (mb_strlen($search_exp_room) > 1 && Request::submitted('search_room')) {
                if (getGlobalPerms($user->id) != "admin")
                    $resList = new ResourcesUserRoomsList ($user->id, FALSE, FALSE);

                $result = $resReq->searchRooms(Request::get('search_exp_room'), FALSE, 0, 10, FALSE, (is_object($resList)) ? array_keys($resList->getRooms()) : FALSE);
            }
            if ($result) {
                echo MessageBox::info(sizeof($result) . " " . _("Ressourcen gefunden:"));
                if($change_schedule_move_or_copy != 'copy'){
                    print "<select name=\"select_change_resource\">";
                    foreach ($result as $key => $val) {
                        printf ("<option value=\"%s\">%s  </option>", $key, htmlReady(my_substr($val, 0, 40)));
                    }
                    print "</select> ";
                    echo Button::create(_('Verschieben'), 'send_change_resource',
                        ['title' => _("Die Belegung in den ausgewählten Raum verschieben")]);
                    echo Button::create(_('Neue Suche'), 'reset_room_search');
                } else {
                    ?>
                    <select name="select_change_resource[]" multiple size="10">
                    <?foreach($result as $key => $name){?>
                        <option value="<?=$key?>"><?=htmlReady(my_substr($name, 0, 40))?>  </option>
                    <?}?>
                    </select>
                    </font>
                    <?= Button::create(_('Kopieren'), 'send_change_resource',
                        ['title' => _("Die Belegung in die ausgewählten Raum kopieren")]); ?>
                    <?= Button::create(_('Neue Suche'), 'reset_room_search'); ?>
                    <?
                }
                echo "<br><br>";
            } else {
                ?>
                <? if(isset($result)) : ?>
                    <?= MessageBox::info(_("<b>Keine</b> Ressource gefunden.")) ?>
                <? endif ?>
                <?=_("Geben Sie zur Suche den Namen der Ressource ganz oder teilweise ein:"); ?>
                <br>
                <input type="text" size="30" maxlength="255" name="search_exp_room">&nbsp;
                <?= Button::create(_('Suchen'), 'search_room') ?>
                <br>
                <?
            }
            ?>
            </td>
            <td valign="top">
            <?
            if (!in_array($resAssign->getRepeatMode(), ['na','sd'])) :
                ?>
                <b><?=_("Regelmäßige Belegung in Einzeltermine umwandeln:")?></b><br><br>
                <?=_("Nutzen Sie diese Funktion, um eine Terminserie in Einzeltermine umzuwandeln. Diese Einzeltermine können dann getrennt bearbeitet werden.");?>
                <br><br>
                <?= Button::create(_('Umwandeln'), 'change_meta_to_single_assigns') ?>
            <?
            endif;
            ?>
            </td>
        </tr>
        <? endif; ?>
    </tbody>
</table>
</form>
 <script>
    jQuery("#changeRepeatTime").datepicker();
</script>
