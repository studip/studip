<?php
    $notice = $course->config->COURSE_ADMIN_NOTICE;
    $method = $notice ? 'createHasNotice' : 'createHasNoNotice';
?>

<?= Studip\LinkButton::$method(
    _('Notiz'),
    URLHelper::getURL(
        sprintf($action['url'], $course->id),
        $action['params'] ?? []
    ),
    array_merge($action['attributes'] ?? [], [
        'class' => 'admin-courses-action-button',
        'title' => $notice,
    ])
) ?>
