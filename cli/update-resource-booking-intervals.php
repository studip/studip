#!/usr/bin/env php
<?php


require_once(__DIR__ . '/studip_cli_env.inc.php');


$keep_exceptions = true;

$options = getopt('h', ['remove-exceptions']);

if (array_key_exists('h', $options)) {
    echo("Usage:\tupdate-resource-booking-intervals.php [--remove-exceptions]\n");
    echo("\tIf --remove-exceptions is set, exceptions for a booking with repetitions\n");
    echo("\twill be removed. By default, they are kept.\n");
    exit(0);
}

if (array_key_exists('remove-exceptions', $options)) {
    $keep_exceptions = false;
    echo("Exceptions in bookings with repetitions will be removed!\n");
}

$bookings = ResourceBooking::findBySql('TRUE');
if (!$bookings) {
    echo("There are no bookings in your database! Nothing to do!\n");
    exit(0);
}
foreach ($bookings as $booking) {
    $booking->updateIntervals($keep_exceptions);
}

echo("End of script. The resource_booking_intervals table is up to date again!\n");
