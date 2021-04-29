<?php
# Lifter010: TODO
unset($freetext['']);

$link       = $link ?? false;
$prefix     = $prefix ?? ', ';
$hide_empty = $hide_empty ?? false;
$limit      = $limit ?? 3;
$assigned   = $assigned ?: [];
$freetext   = $freetext ?: [];

if ($assigned || $freetext) {
    if ($assigned) {
        $rooms = $plain ? getPlainRooms($assigned) : getFormattedRooms($assigned, $link);
    }

    if ($freetext) {
        foreach ($freetext as $name => $count) {
            if ($name) {
                $rooms[] = '(' . ($plain ? $name : formatReady($name)) . ')';
            }
        }
    }

    echo $prefix . _('Ort') . ': ';
    echo implode(', ', array_slice($rooms, 0, $limit));
    if (count($rooms) > $limit) {
        printf(_(' (+%s weitere)'), count($rooms) - $limit);
    }
} elseif (!$hide_empty) {
    echo ' ' . _('k.A.');
}
