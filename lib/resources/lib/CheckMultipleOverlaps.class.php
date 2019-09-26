<?php
# Lifter002: DONE - not applicable
# Lifter003: TEST
# Lifter007: TODO  methods need documentation
# Lifter010: DONE - not applicable

/**
* CheckMultipleOverlaps.class.php
*
* checks overlaps for multiple resources, seminars and assign objects
* via a special table
*
*
* @author       Cornelis Kater <ckater@gwdg.de>, data-quest GmbH <info@data-quest.de>
* @access       public
* @package      resources
* @modulegroup      resources_modules
* @module       CheckMultipleOverlaps.class.php
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// CheckMultipleOverlaps.class.php
// Klasse zum checken von Ueberschneidungen von mehrere Ressourcen, Veranstaltungen und
// Belegungen
// Copyright (C) 2004 Cornelis Kater <ckater@gwdg.de>, data-quest GmbH <info@data-quest.de>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+

class CheckMultipleOverlaps
{
    var $begin;
    var $end;
    var $resource_ids;      //all the resources in the actual check-set

    function setTimeRange($begin, $end)
    {
        $this->begin = $begin;
        $this->end   = $end;
    }

    function setAutoTimeRange($assObjs)
    {
        $end = 0;
        foreach ($assObjs as $obj) {
            if (!$begin) {
                $begin = $obj->getBegin();
            }
            if ($obj->getBegin() < $begin) {
                $begin = $obj->getBegin();
            }
            if ($obj->getRepeatEnd() > $end) {
                $end = $obj->getRepeatEnd();
            }
        }
        $this->setTimeRange($begin, $end);
    }

    function addResource($resource_id)
    {
        // check, if the added resources needs to be checked
        $resObj = ResourceObject::Factory($resource_id);

        if (!$resObj->getMultipleAssign()) {
            if (!$this->begin || !$this->end) {
                throw new RuntimeException(__METHOD__ . ' could not add resource without time range');
            }
            $this->resource_ids[] = $resource_id;
            $parameters = [];
            $query = "SELECT DISTINCT assign_id
                FROM resources_assign ra
                LEFT JOIN resources_temporary_events rte USING(assign_id,resource_id)
                WHERE rte.event_id IS NULL AND
                ra.resource_id = :resource_id AND
                (ra.begin BETWEEN :begin AND :end OR (ra.begin <= :end AND (ra.repeat_end > :begin OR ra.end > :begin)))";
            $parameters[':resource_id'] = $resource_id;
            $parameters[':begin'] = $this->begin;
            $parameters[':end']   = $this->end;
            $statement = DBManager::get()->prepare($query);
            $statement->execute($parameters);
            $missing_temporary_assigns = $statement->fetchAll(PDO::FETCH_COLUMN);
            if (count($missing_temporary_assigns)) {
                foreach ($missing_temporary_assigns as $assign_id) {
                    $assign = new AssignObject($assign_id);
                    $assign->updateResourcesTemporaryEvents();
                }
            }
            return true;
        }
        return false;
    }

    function checkOverlap ($events, &$result, $index_mode = 'assign_id')
    {
        if (!$events) {
            return false;
        }
        if (!$this->resource_ids) {
            $result = [];
            return;
        }

        $parameters = $assign_ids = [];
        $cases = '';
        foreach (array_values($events) as $i => $obj) {
            $cases .= "WHEN begin < :end{$i} AND end > :begin{$i} THEN :id{$i} ";

            $parameters['begin' . $i] = $begin[] = (int) $obj->getBegin();
            $parameters['end' . $i]   = $end[]   = (int) $obj->getEnd();
            $parameters['id' . $i] = $obj->getId();

            $assign_ids[] = $obj->assign_id;
        }

        $query = "SELECT resource_id, `begin`, end, assign_id,
                         CASE {$cases} END AS event_id
                  FROM resources_temporary_events
                  WHERE begin < :end_max AND end > :begin_min
                    AND resource_id IN (:resource_ids)
                    AND assign_id NOT IN (:assign_ids)
                  HAVING event_id IS NOT NULL
                  ORDER BY begin";
        $parameters[':end_max']      = max($end);
        $parameters[':begin_min']    = min($begin);
        $parameters[':resource_ids'] = $this->resource_ids;
        $parameters[':assign_ids']   = $assign_ids;

        $statement = DBManager::get()->prepare($query);
        $statement->execute($parameters);
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $index = ($index_mode == 'assign_id')
                   ? $events[$row['event_id']]->getAssignId()
                   : $events[$row['event_id']]->getAssignUserId();
            $result[$row['resource_id']][$index][] = [
                'begin'     => $row['begin'],
                'end'       => $row['end'],
                'assign_id' => $row['assign_id'],
                'event_id'  => $row['event_id'],
                'own_begin' => $events[$row['event_id']]->getBegin(),
                'own_end'   => $events[$row['event_id']]->getEnd(),
            ];
        }
    }
}
