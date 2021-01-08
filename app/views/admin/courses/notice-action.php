<?php
    $notice = false;
    $course->datafields->each(function ($datafield) use (&$notice) {
        if ($datafield->name === 'Notiz zu einer Veranstaltung') {
            $notice = $datafield->content;
        }
    });

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
