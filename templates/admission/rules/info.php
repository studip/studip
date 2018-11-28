<i><?= _('Nachricht bei nicht erfolgreicher Anmeldung:') ?></i>
<br/>
<?= $rule->getMessage() ?>
<?php
if ($rule->getStartTime() && $rule->getEndTime()) {
    echo '<br/>'.sprintf(_('Diese Regel gilt von %s bis %s'), strftime('%d.%m.%Y %H:%M',
        $rule->getStartTime()), strftime('%d.%m.%Y %H:%M', $rule->getEndTime()));
} else if ($rule->getStartTime() && !$rule->getEndTime()) {
    echo '<br/>'.sprintf(_('Diese Regel gilt ab %s'), strftime('%d.%m.%Y %H:%M', $rule->getStartTime()));
} else if (!$rule->getStartTime() && $rule->getEndTime()) {
    echo '<br/>'.sprintf(_('Diese Regel gilt bis %s'), strftime('%d.%m.%Y %H:%M', $rule->getEndTime()));
}
