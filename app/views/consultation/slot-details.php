<?= strftime('%A, %x', $slot->start_time) ?>
 -
<?= sprintf(
    _('%s bis %s Uhr'),
    strftime('%R', $slot->start_time),
    strftime('%R', $slot->end_time)
) ?>
