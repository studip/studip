<?php
$seminar = get_object_name($topic['seminar_id'], 'sem');
array_pop($path); // last element is the entry itself

$message = [
   'header' => sprintf(
        _('Im Forum der Veranstaltung **%s** gibt es einen neuen Beitrag unter **%s** von **%s**'),
        $seminar['name'],
        implode(' > ', array_map(function ($p) { return $p['name']; }, $path)),
        $topic['anonymous'] ? _('Anonym') : $topic['author']
    ),
    'title' => $topic['name'] ? '**' . $topic['name'] ."** \n\n" : '',
    'content' => $topic['content'],
    'url' => _('Beitrag im Forum ansehen:') .' '. UrlHelper::getUrl(
        $GLOBALS['ABSOLUTE_URI_STUDIP']
        . 'plugins.php/coreforum/index/index/'
        . $topic['topic_id']
        .'?cid='
        . $topic['seminar_id']
        .'&again=yes#'
        . $topic['topic_id']
    )
];

// since we've possibly got a mixup of HTML and Stud.IP markup,
// create a pure HTML message step by step
$htmlMessage = Studip\Markup::markAsHtml(
    implode('<br><br>', array_map('formatReady', $message))
);

echo $htmlMessage;
