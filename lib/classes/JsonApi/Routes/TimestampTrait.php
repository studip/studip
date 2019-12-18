<?php

namespace JsonApi\Routes;

trait TimestampTrait
{
    protected static function fromISO8601($strISO8601Timestamp)
    {
        if ($atom = date_create_from_format(\DATE_ATOM, $strISO8601Timestamp)) {
            return $atom;
        }

        return date_create_from_format('Y-m-d\TH:i:s.uP', $strISO8601Timestamp);
    }

    protected static function isValidTimestamp($strISO8601Timestamp)
    {
        return false !== self::fromISO8601($strISO8601Timestamp);
    }
}
