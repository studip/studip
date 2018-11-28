<?php
if ($rule->getStartTime() && $rule->getEndTime()) {
    echo sprintf(_('Diese Regel gilt von %s bis %s.'), strftime('%d.%m.%Y %H:%M',
        $rule->getStartTime()), strftime('%d.%m.%Y %H:%M', $rule->getEndTime())).'<br/>';
} else if ($rule->getStartTime() && !$rule->getEndTime()) {
    echo sprintf(_('Diese Regel gilt ab %s.'), strftime('%d.%m.%Y %H:%M', $rule->getStartTime())).'<br/>';
} else if (!$rule->getStartTime() && $rule->getEndTime()) {
    echo sprintf(_('Diese Regel gilt bis %s.'), strftime('%d.%m.%Y %H:%M', $rule->getEndTime())).'<br/>';
}
?>
<?= sprintf(_('Die Anmeldung zu maximal %s Veranstaltungen des Anmeldesets ist '.
    'erlaubt.'), $rule->getMaxNumber()); ?>
