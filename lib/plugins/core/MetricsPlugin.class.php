<?php
/*
 * MetricsPlugins take countings and measurements and transfer them to
 * a specific backend like statsd.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      <mlunzena@uos.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

interface MetricsPlugin
{
    public static function count($stat, $value, $sampleRate = null);

    public static function timing($stat, $time, $sampleRate = null);

    public static function gauge($stat, $value, $sampleRate = null);
}
