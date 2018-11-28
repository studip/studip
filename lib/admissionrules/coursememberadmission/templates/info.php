<?php
if ($rule->getStartTime() && $rule->getEndTime()) {
    echo sprintf(_('Diese Regel gilt von %s bis %s.'), strftime('%d.%m.%Y %H:%M',
        $rule->getStartTime()), strftime('%d.%m.%Y %H:%M', $rule->getEndTime())).'<br/>';
} else if ($rule->getStartTime() && !$rule->getEndTime()) {
    echo sprintf(_('Diese Regel gilt ab %s.'), strftime('%d.%m.%Y %H:%M', $rule->getStartTime())).'<br/>';
} else if (!$rule->getStartTime() && $rule->getEndTime()) {
    echo sprintf(_('Diese Regel gilt bis %s.'), strftime('%d.%m.%Y %H:%M', $rule->getEndTime())).'<br/>';
}
$course = Course::find($rule->mandatory_course_id);
if ($course) {
echo sprintf(!$rule->modus ?
    _('Die Anmeldung ist nur Teilnehmenden der Veranstaltung: <b>%s</b> %s erlaubt.') :
    _('Die Anmeldung ist für Teilnehmende der Veranstaltung: <b>%s</b> %s verboten.'),
    $course->getFullname('number-name-semester'), '<a href="'.URLHelper::getScriptLink('dispatch.php/course/details/index/' . $course->id).'"  data-dialog>'.
        Icon::create('info-circle', 'inactive', ['title' =>_('Veranstaltungsdetails aufrufen')])->asImg().'</a>');
}
