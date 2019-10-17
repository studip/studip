<?php
# Lifter010: TODO
/*
 * NotificationCenter.class.php - NotificationCenter class
 *
 * Copyright (c) 2009  Elmar Ludwig
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

// ########################################################################
//  MODULE DEFINITION
/** @defgroup notifications Notifications */

/**
 * Special Exception that can be thrown to veto an announced change.
 * Only some types of events support this (see documentation).
 */
class NotificationVetoException extends Exception
{
}

/**
 * The NotificationCenter class is the central event dispatcher
 * for Stud.IP. Objects interested in receiving notifications for
 * particular events need to register with the NotificationCenter:
 *
 * NotificationCenter::addObserver($this, 'update', 'shutdown');
 *
 * Event notifications are sent via the postNotification() method:
 *
 * NotificationCenter::postNotification('shutdown', $sender);
 */
class NotificationCenter
{
    /**
     * array of registered notification observers
     */
    private static $observers = [];

    /**
     * Register an object to be notified. The same object may be
     * registered several times (e.g. for different notifications).
     * The event name may contain shell-style wildcards (like '*').
     *
     * @param object $observer  object to be notified
     * @param string $method    method that will be called
     * @param string $event     name of event (may be NULL)
     * @param mixed  $object    subject to observe (may be NULL)
     */
    public static function addObserver($observer, $method, $event, $object = NULL)
    {
        if ($event === NULL) {
            $event = '';
        }

        if ($object) {
            $predicate = is_callable($object)
                ? $object
                : function ($other) use ($object) {
                    return $object === $other;
                  };
        }

        self::$observers[$event][] =
            ['predicate' => $predicate ?: NULL,
                  'observer'  => [$observer, $method]];
    }

    /**
     * Remove an object registered with the NotificationCenter.
     * Trying to remove an observer that was not registered is
     * allowed and has no effect.
     *
     * @param object $observer  object to be removed
     * @param string $event     name of event (may be NULL)
     * @param mixed  $object    subject to observe (may be NULL)
     */
    public static function removeObserver($observer, $event = NULL, $object = NULL)
    {
        if ($event === NULL) {
            $events = array_keys(self::$observers);
        } else if (isset(self::$observers[$event])) {
            $events = [$event];
        } else {
            return;
        }

        foreach ($events as $event) {
            foreach (self::$observers[$event] as $index => $list) {
                if ($object === NULL
                    || $list['predicate'] && $list['predicate']($object)) {

                    if ($list['observer'][0] === $observer) {
                        unset(self::$observers[$event][$index]);
                    }
                }
            }
        }
    }

    /**
     * Post an event notification to all registered observers.
     * Only observers registered for this event type and subject
     * are notified.
     *
     * @param string $event     name of this notification
     * @param mixed  $object    subject of this notification
     * @param mixed  $user_data additional information (optional)
     *
     * @throws NotificationVetoException  on observer veto
     */
    public static function postNotification($event, $object, $user_data = null)
    {
        $current_observers = [];
        foreach (self::$observers as $e => $l) {
            if (self::eventMatchesRequirement($e, $event)) {
                $current_observers = array_merge($current_observers, $l);
            }
        }

        foreach ($current_observers as $list) {
            if (!$list['predicate'] || $list['predicate']($object)) {
                call_user_func($list['observer'], $event, $object, $user_data);
            }
        }
    }

    /**
     * Determines whether the given event matches the required event.
     *
     * @param  string $event    name of the given notification
     * @param  string $required name of the required notification
     * @return bool
     */
    private static function eventMatchesRequirement($event, $required)
    {
        // Catchall event matches always
        if ($event === '' || $event === '*') {
            return true;
        }

        // No wildcard event notification name, names must match
        if ($event[mb_strlen($strlen) - 1] !== '*') {
            return $event === $required;
        }

        // Otherwise, the event must match the required event at the beginning
        return mb_strpos($required, mb_substr($event, 0, -1)) === 0;
    }

    /**
     * Convenience method that uses a jQuery like structure for event
     * registration by closures.
     *
     * @param string   $event
     * @param Callable $callback
     * @param mixed    $object
     * @since Stud.IP 4.2
     */
    public static function on($event, Callable $callback, $object = null)
    {
        if ($callback instanceof Closure || is_object($callback)) {
            static::addObserver($callback, '__invoke', $event, $object);
        } elseif (is_array($callback)) {
            static::addObserver($callback[0], $callback[1], $event, $object);
        } elseif (is_string($callback)) {
            throw new Exception('Strings as callable may not be passed to ' . __METHOD__);
        }
    }

    /**
     * Convenience method that uses a jQuery like structure for event
     * unregistration by closures.
     *
     * @param string   $event
     * @param Callable $callback
     * @param mixed    $object
     * @since Stud.IP 4.2
     */
    public static function off($event, Callable $callback, $object = null)
    {
        if ($callback instanceof Closure || is_object($callback)) {
            static::removeObserver($callback, $event, $object);
        } elseif (is_array($callback)) {
            static::removeObserver($callback[0], $event, $object);
        } elseif (is_string($callback)) {
            throw new Exception('Strings as callable may not be passed to ' . __METHOD__);
        }
    }
}
