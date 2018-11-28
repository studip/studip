<?php
$text = '';
if ($rule->getStartTime() && !$rule->getEndTime()) {
    $text = sprintf(_("Die Anmeldung ist möglich ab %s."), date("d.m.Y, H:i", 
        $rule->startTime));
} else if (!$rule->getStartTime() && $rule->getEndTime()) {
    $text = sprintf(_("Die Anmeldung ist möglich bis %s."), date("d.m.Y, H:i", 
        $rule->endTime));
} else if ($rule->getStartTime() && $rule->getEndTime()) {
    $text = sprintf(_("Die Anmeldung ist möglich von %s bis %s."), 
        date("d.m.Y, H:i", $rule->startTime), date("d.m.Y, H:i", $rule->endTime));
}
?>
<?= $text ?>
