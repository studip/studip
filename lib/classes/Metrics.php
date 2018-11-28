<?php
/*
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      <mlunzena@uos.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

/**
 * Front-end proxy to a metrics collection service provided by
 * installed MetricsPlugins.
 *
 * Send metrics to the metrics collection service using gauges,
 * counters or timers (depending on the type of stat you want to measure):
 *
 * @code
 * // send a metric that counts something e.g. number of users logging in:
 * Metrics::count('core.user_login', 1);
 * // use the shortcut:
 * Metrics::increment('core.user_login');
 *
 * // send a metric that represents an instantaneous measurement of a
 * // value like number of currently logged in users or the number of
 * // courses currently available
 * Metrics::gauge('core.global_blubbers', 173723);
 *
 * // send a metric that measures the number of milliseconds elapsed
 * // between a start and end time e.g. milliseconds spent searching
 * // for a course using the Stud.IP quicksearch
 * $start_time = microtime(true) * 1000;
 *
 * // now do something for some time
 *
 * $end_time = microtime(true) * 1000;
 *
 * Metrics::timing('core.quick_searched', $end_time - $start_time);
 *
 * // use Metrics::startTimer instead:
 * $timer = Metrics::startTimer();
 *
 * // now do something for some time
 *
 * // send the measured milliseconds since calling Metrics::startTimer
 * $timer('core.quick_searched');
 *
 * // sample rates (a float between 0 and 1) may be given to only send
 * // data this percentage of the time:
 * Metrics::count('core.user_login', 1, 0.1);
 *
 * Metrics::increment('core.user_login', 0.2);
 *
 * Metrics::gauge('core.global_blubbers', 173723, 0.3);
 *
 * Metrics::timing('core.request_time', 747, 0.4);
 *
 * $timer = Metrics::startTimer();
 * $timer('core.quick_searched', 42, 0.5);
 * @endcode
 *
 * Please note: Names of stats must be strings containing lowercase
 * characters or underscores. You may use `.` (dots) to namespace
 * them. Metrics send from Stud.IP core code should start with 'core.'
 * for example 'core.request_time'.
 *
 * @author  <mlunzena@uos.de>
 * @license GPL 2 or later
 * @since   Stud.IP 3.1
 */
class Metrics {

    /**
     * Increment a counter.
     *
     * @param string $stat  the name of the counter
     * @param integer $increment  the amount to increment by; must be within [-2^63, 2^63]
     * @param float $sampleRate  a float between 0 and 1; will only be send this percentage of time
     */
    public static function count($stat, $increment, $sampleRate = 1)
    {
        self::sendMessage('count', $stat, intval($increment), $sampleRate);
    }

    /**
     * Increment a counter by +1.
     *
     * @param string $stat  the name of the counter
     * @param float $sampleRate  a float between 0 and 1; will only be send this percentage of time
     */
    public static function increment($stat, $sampleRate = 1)
    {
        self::count($stat, 1, $sampleRate);
    }

    /**
     * Increment a counter by -1.
     *
     * @param string $stat  the name of the counter
     * @param float $sampleRate  a float between 0 and 1; will only be send this percentage of time
     */
    public static function decrement($stat, $sampleRate = 1)
    {
        self::count($stat, -1, $sampleRate);
    }


    /**
     * Set a gauge value.
     *
     * @param string $stat  the name of the gauge
     * @param integer $value  the value of the gauge; must be within [0, 2^64]
     * @param float $sampleRate  a float between 0 and 1; will only be send this percentage of time
     */
    public static function gauge($stat, $value, $sampleRate = 1)
    {
        if ($value < 0) {
            throw new InvalidArgumentException("Valid gauge values are in the range [0, 2^64]");
        }
        self::sendMessage('gauge', $stat, intval($value), $sampleRate);
    }

    /**
     * Record a timing.
     *
     * @param string $stat  the name of the counter
     * @param integer $milliseconds  the amount to milliseconds that something lastedincrement by; must be within [0, 2^64]
     * @param float $sampleRate  a float between 0 and 1; will only be send this percentage of time
     */
    public static function timing($stat, $milliseconds, $sampleRate = 1)
    {
        if ($milliseconds < 0) {
            throw new InvalidArgumentException("Valid timer values are in the range [0, 2^64]");
        }
        self::sendMessage('timing', $stat, intval($milliseconds), $sampleRate);
    }


    /**
     * Return a timer function that you may invoke to send the
     * recorded time between calling Metrics::startTimer and calling
     * its resulting timer.
     *
     * The timer function has this signature:
     *
     * @code
     * $timer = function ($stat, $sampleRate = 1) {...};
     * @endcode
     *
     * Invoke the timer function using a stat name and an optional
     * sample rate:
     *
     * @code
     * $timer('core.sampleTiming');
     * // or ...
     * $timer('core.sampleTiming', 0.1);
     *
     * @endcode
     *
     * @return function  the timing function
     */
    public static function startTimer()
    {
        $start_time = microtime(true);
        return function ($stat, $sampleRate = 1) use ($start_time) {
            \Metrics::timing($stat, round(1000 * (microtime(true) - $start_time)), $sampleRate);
        };
    }


    // cache the metric plugins to increase performance
    private static $metricPlugins;

    // retrieve all activated MetricsPlugins and send them the stat
    // type, name and value
    private static function sendMessage($message, $stat, $value, $sampleRate)
    {
        // cannot proceed without loaded PluginEngine
        if (!class_exists('PluginEngine')) {
            return;
        }

        if ($sampleRate < 1) {
            $rand = mt_rand() / mt_getrandmax();
            if ($rand > $sampleRate) {
                return;
            }
        }

        // cache the activated MetricsPlugins
        if (!self::$metricPlugins) {
            self::$metricPlugins = \PluginEngine::getPlugins('MetricsPlugin');
        }

        // call every MetricPlugin
        foreach (self::$metricPlugins as $plugin) {
            call_user_func_array(array($plugin, $message), array($stat, $value, $sampleRate));
        }
    }
}
