<?php
if ($count = count($studycourses)) {
    $studycourse = $studycourses->first();
    echo sprintf(
        '%s (%s)',
        htmlReady(trim($studycourse->studycourse_name . ' ' . $studycourse->degree_name)),
        htmlReady($studycourse->semester)
    );;
    if ($count > 1) {
        echo '[...]';
        $course_res = implode("\n", $studycourses->limit(1, PHP_INT_MAX)->map(function ($item) {
            return sprintf(
                '- %s (%s)<br>',
                htmlReady(trim($item->studycourse_name . ' ' . $item->degree_name)),
                htmlReady($item->semester)
            );
        }));
        echo tooltipHtmlIcon('<strong>' . _('Weitere Studiengänge') . '</strong><br>' . $course_res);
    }
}
?>
