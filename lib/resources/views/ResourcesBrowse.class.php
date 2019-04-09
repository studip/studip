<?php
# Lifter002: TODO
# Lifter003: TEST
# Lifter007: TODO
# Lifter010: TODO
/**
* ResourcesBrowse.class.php
*
* search egine for resources
*
*
* @author       Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup      resources
* @module       ResourcesBrowse.class.php
* @package      resources
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ResourcesBrowse.class.php
// die Suchmaschine fuer Ressourcen
// Copyright (C) 2003 Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
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

use Studip\Button,
    Studip\LinkButton;

require_once 'lib/resources/views/ShowList.class.php';


/*****************************************************************************
ResourcesBrowse, the search engine
/*****************************************************************************/

class ResourcesBrowse {
    var $start_object;      //where to start
    var $open_object;       //where we stay
    var $mode;          //the search mode
    var $searchArray;       //the array of search expressions (free search & properties)

    function __construct() {
        $this->list = new ShowList;

        $this->list->setRecurseLevels(0);
        $this->list->setViewHiearchyLevels(FALSE);
    }

    function setStartLevel($resource_id) {
        $this->start_object = $resource_id;
    }

    function setOpenLevel($resource_id) {
        $this->open_object = $resource_id;
    }

    function setMode($mode="browse") {
        $this->mode=$mode;
        if (!$this->mode)
            $this->mode="browse";
    }

    function setCheckAssigns($value) {
        $this->check_assigns=$value;
    }

    function setSearchOnlyRooms($value){
        $this->search_only_rooms = $this->list->show_only_rooms = $value;
    }

    function setSearchArray($array) {
        $this->searchArray = $array;
    }

    private function searchFormHeader()
    {
        ?>
        <fieldset>
            <legend><?= _('Ressource suchen') ?></legend>
            <label class="col-3">
                <?= _('Bezeichnung')?>
                <input name="search_exp" type="text" placeholder="<?= _('Name der Ressource') ?>" autofocus
                       value="<? echo htmlReady(stripslashes($this->searchArray["search_exp"])); ?>">
            </label>
            <label class="col-3">
                <?= _('Freie Suche') ?>
                <select>
                    <option value="0" selected><?=htmlReady(Config::get()->UNI_NAME_CLEAN)?></option>
                    <?if ($this->open_object){
                        $res = ResourceObject::Factory($this->open_object);
                        ?>
                        <option value="<?=$this->open_object?>" selected><?=htmlReady($res->getName())?></option>
                    <?}?>
                </select>
            </label>
        </fieldset>

        <?
    }

    private function searchFormFooter()
    {
        ?>
        <footer>
            <?= Button::create(_('Suchen'), 'start_search') ?>
            <?= LinkButton::create(_('Zurücksetzen'), URLHelper::getURL('?view=search&quick_view_mode=' . $GLOBALS['view_mode'] . '&reset=TRUE')) ?>
        </footer>
        <?
    }

    //private
    function getHistory($id)
    {
        $query = "SELECT name, parent_id, resource_id, owner_id
                  FROM resources_objects
                  WHERE resource_id = ? ORDER BY name";
        $statement = DBManager::get()->prepare($query);

        $result_arr = [];
        while ($id) {
            $statement->execute([$id]);
            $object = $statement->fetch(PDO::FETCH_ASSOC);
            $statement->closeCursor();

            $result_arr[] = [
                'id'       => $object['resource_id'],
                'name'     => $object['name'],
                'owner_id' => $object['owner_id']
            ];
            $id = $object['parent_id'];
        }

        if (count($result_arr) > 0)
            switch (ResourceObject::getOwnerType($result_arr[count($result_arr)-1]["owner_id"])) {
                case "global":
                    $top_level_name = Config::get()->UNI_NAME_CLEAN;
                break;
                case "sem":
                    $top_level_name = _("Veranstaltungsressourcen");
                break;
                case "inst":
                    $top_level_name = _("Einrichtungsressourcen");
                break;
                case "fak":
                    $top_level_name = _("Fakultätsressourcen");
                break;
                case "user":
                    $top_level_name = _("persönliche Ressourcen");
                break;
            }

            if ($GLOBALS['view'] == 'search') {
                $result  = '<a href="'. URLHelper::getLink('?view=search&quick_view_mode='. $GLOBALS['view_mode'] .'&reset=TRUE') .'">';
                $result .=  $top_level_name;
                $result .= '</a>';
            }

            for ($i = sizeof($result_arr)-1; $i>=0; $i--) {
                if ($GLOBALS['view']) {
                    $result .= ' &gt; <a href="'.URLHelper::getLink(sprintf('?quick_view='.$GLOBALS['view'].'&quick_view_mode='.$GLOBALS['view_mode'].'&%s='.$result_arr[$i]["id"],($GLOBALS['view']=='search') ? "open_level" : "actual_object" ) );

                    $result .= '">'. htmlReady($result_arr[$i]["name"]) .'</a>';
                } else {
                    $result.= sprintf (" &gt; %s", htmlReady($result_arr[$i]["name"]));
                }
            }
        return $result;
    }

    //private
    function showTimeRange()
    {
        $weekday_options = [
            '-1'        => _('--'),
            'Monday'    => _('Montag'),
            'Tuesday'   => _('Dienstag'),
            'Wednesday' => _('Mittwoch'),
            'Thursday'  => _('Donnerstag'),
            'Friday'    => _('Freitag'),
            'Saturday'  => _('Samstag'),
            'Sunday'    => _('Sonntag'),
        ];

        $all_semester = SemesterData::getAllSemesterData();
        if (!$this->searchArray['search_semester']) {
            $current_semester = SemesterData::getCurrentSemesterData();
            $selected_semester = SemesterData::getSemesterDataByDate(strtotime('+1 day',$current_semester['ende']));
        } else {
            $selected_semester['semester_id'] = $this->searchArray['search_semester'];
        }

        $semesters = [];
        foreach (array_reverse($all_semester) as $semester) {
            $semesters[$semester['semester_id']] = [
                'label'    => $semester['name'],
                'selected' => $selected_semester['semester_id'] == $semester['semester_id'],
            ];

        }
        ?>
        <fieldset>
            <legend>
                <?= _('gefundene Ressourcen sollen zu folgender Zeit <u>nicht</u> belegt sein:') ?>
            </legend>

            <table class="default nohover">
                <colgroup>
                    <col width="20%">
                    <col width="20%">
                    <col width="20%">
                    <col width="20%">
                </colgroup>
                <tbody>
                    <tr>
                        <th colspan="4"><?= _('Einzeltermin:') ?></th>
                    </tr>
                    <tr>
                        <td>
                            <?= _('Beginn') ?>:
                            <input type="text" name="search_begin_hour"
                                   size="2" maxlength="2"
                                   placeholder="<?= _('HH') ?>"
                                   value="<?= $this->searchArray['search_assign_begin'] ? date('H', $this->searchArray['search_assign_begin']) : '' ?>"
                                   class="no-hint" style="width:5ex">
                            :
                            <input type="text" name="search_begin_minute"
                                   size="2" maxlength="2"
                                   placeholder="<?= _('mm') ?>"
                                   value="<?= $this->searchArray['search_assign_begin'] ? date('i', $this->searchArray['search_assign_begin']) : '' ?>"
                                   class="no-hint" style="width:5ex">

                            <?= _('Uhr') ?>
                        </td>
                        <td>
                            <?= _('Ende') ?>:
                            <input type="text" name="search_end_hour"
                                   size="2" maxlength="2"
                                   placeholder="<?= _('HH') ?>"
                                   value="<?= $this->searchArray['search_assign_end'] ? date('H', $this->searchArray['search_assign_end']) : '' ?>"
                                   class="no-hint" style="width:5ex">
                            :
                            <input type="text" name="search_end_minute"
                                   size="2" maxlength="2"
                                   placeholder="<?= _('mm') ?>"
                                   value="<?= $this->searchArray['search_assign_end'] ? date('i', $this->searchArray['search_assign_end']) : '' ?>"
                                   class="no-hint" style="width:5ex">

                            <?= _('Uhr') ?>
                        </td>
                        <td>
                            <?= _('Datum') ?>:
                            <input name="searchDate" size="10"
                                   value="<?= $this->searchArray['search_assign_begin'] ? date('j.m.Y', $this->searchArray['search_assign_begin']) : '' ?>"
                                   data-date-picker>
                        </td>
                        <td>
                            <label>
                                <input type="checkbox" name="search_repeating"
                                       value="1"
                                       <? if ($this->searchArray['search_repeating']) echo 'checked'; ?>>
                                <?= _('für restliches Semester prüfen') ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th colspan="4"><?= _('Semestertermin:') ?></th>
                    </tr>
                    <tr>
                        <td>
                            <?= _('Beginn') ?>:
                            <input type="text" name="search_begin_hour_2"
                                   size="2" maxlength="2"
                                   placeholder="<?= _('HH') ?>"
                                   value="<?= $this->searchArray['search_assign_begin'] ? date('H', $this->searchArray['search_assign_begin']) : '' ?>"
                                   class="no-hint" style="width:5ex">
                            :
                            <input type="text" name="search_begin_minute_2"
                                   size="2" maxlength="2"
                                   placeholder="<?= _('mm') ?>"
                                   value="<?= $this->searchArray['search_assign_begin'] ? date('i', $this->searchArray['search_assign_begin']) : '' ?>"
                                   class="no-hint" style="width:5ex">

                             <?= _('Uhr') ?>
                        </td>
                        <td>
                            <?= _('Ende') ?>:
                            <input type="text" name="search_end_hour_2"
                                   size="2" maxlength="2"
                                   placeholder="<?= _('HH') ?>"
                                   value="<?= $this->searchArray['search_assign_end'] ? date('H', $this->searchArray['search_assign_end']) : '' ?>"
                                   class="no-hint" style="width:5ex">
                            :
                            <input type="text" name="search_end_minute_2"
                                   size="2" maxlength="2"
                                   placeholder="<?= _('mm') ?>"
                                   value="<?= $this->searchArray['search_assign_end'] ? date('i', $this->searchArray['search_assign_end']) : '' ?>"
                                   class="no-hint" style="width:5ex">

                             <?= _('Uhr') ?>
                        </td>
                        <td>
                            <label>
                                <?= _('Tag der Woche') ?>:
                                <select name="search_day_of_week">
                                <? foreach ($weekday_options as $key => $label): ?>
                                    <option value="<?= $key ?>" <? if ($this->searchArray['search_day_of_week'] == $key) echo 'selected'; ?>>
                                        <?= $label ?>
                                    </option>
                                <? endforeach; ?>
                                </select>
                            </label>
                        </td>
                        <td>
                            <label>
                                <?=_('Semester')?>:
                                <select name="search_semester">
                                <? foreach ($semesters as $id => $semester): ?>
                                    <option value="<?= htmlReady($id) ?>" <? if ($semester['selected']) echo 'selected'; ?>>
                                        <?= htmlReady($semester['label']) ?>
                                    </option>
                                <? endforeach; ?>
                                </select>
                            </label>
                        </td>
                    </tr>
                </tbody>
            </table>
        </fieldset>
        <?
    }

    //private
    function showProperties()
    {
        $query = "SELECT category_id, name
                  FROM resources_categories
                  ORDER BY name";
        $statement = DBManager::get()->query($query);
        $categories = $statement->fetchGrouped(PDO::FETCH_ASSOC);

        $query = "SELECT property_id, name, type, options
                  FROM resources_categories_properties
                  LEFT JOIN resources_properties USING (property_id)
                  WHERE category_id = ?";
        if (Config::get()->RESOURCES_SEARCH_ONLY_REQUESTABLE_PROPERTY) {
            $query .= " AND requestable = 1";
        }
        $query .= " ORDER BY name";

        $statement = DBManager::get()->prepare($query);

        foreach (array_keys($categories) as $id) {
            $statement->execute([$id]);
            $categories[$id]['properties'] = $statement->fetchAll(PDO::FETCH_ASSOC);
            $statement->closeCursor();
        }
        $categories = array_filter($categories, function ($category) {
            return count($category['properties']) > 0;
        });
        ?>
        <fieldset>
            <legend>
                <?= _('folgende Eigenschaften soll die Ressource besitzen (leer bedeutet egal):') ?>
            </legend>

            <table class="default nohover">
                <colgroup>
                    <col width="15%">
                    <col width="35%">
                    <col width="15%">
                    <col width="35%">
                </colgroup>
            <? foreach ($categories as $id => $category): ?>
                <tbody style="vertical-align: top;">
                    <tr>
                        <th colspan="4"><?= htmlReady($category['name']) ?></th>
                    </tr>
                    <tr>
                    <?  $i = 0;
                        foreach ($category['properties'] as $property):
                        $value = $this->searchArray['properties'][$property['property_id']] ?: false;
                    ?>
                    <? if ($i++ && ($i % 2 === 1)): ?>
                        </tr><tr>
                    <? endif; ?>
                        <td>
                            <input type="hidden" name="search_property_val[]" value="_id_<?= htmlReady($property['property_id']) ?>">
                            <label for="item-<?= htmlReady($id) ?>-<?= $i ?>">
                                <?= htmlReady($property['name']) ?>
                            </label>
                        </td>
                        <td>
                        <? if ($property['type'] === 'bool') :?>
                            <label>
                                <input type="checkbox" name="search_property_val[]"
                                       id="item-<?= htmlReady($id) ?>-<?= $i ?>"
                                       <? if ($value) echo 'checked'; ?>>
                                <?= htmlReady($property['options']) ?>
                            </label>
                        <? elseif ($property['type'] === 'num'): ?>
                            <input type="text" name="search_property_val[]"
                                   id="item-<?= htmlReady($id) ?>-<?= $i ?>"
                                   value="<?= htmlReady($value) ?>"
                                   size="20" maxlength="255"
                                   class="no-hint">
                        <? elseif ($property['type'] === 'text'): ?>
                            <textarea name="search_property_val[]" cols="20" rows="2" id="item-<?= htmlReady($id) ?>-<?= $i ?>"
                            ><?= htmlReady($value) ?></textarea>
                        <? elseif ($property['type'] === 'select'):
                            $options = explode(';', $property['options']);
                        ?>
                            <select name="search_property_val[]" id="item-<?= htmlReady($id) ?>-<?= $i ?>">
                            <option value="">--</option>
                            <? foreach ($options as $a): ?>
                                <option value="<?= htmlReady($a) ?>" <? if ($value == $a) echo 'selected'; ?>>
                                    <?= htmlReady($a) ?>
                                </option>
                            <? endforeach; ?>
                            </select>
                        <? endif; ?>
                        </td>
                    <? endforeach; ?>
                    <? if ($i % 2 !== 0): ?>
                        <td colspan="2"></td>
                    <? endif; ?>
                    </tr>
                </tbody>
            <? endforeach; ?>
            </table>
        </fieldset>
        <?
    }

    //private
    function browseLevels()
    {
        $parameters = [];
        if ($this->open_object) {
            $query = "SELECT parent_id FROM resources_objects WHERE resource_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$this->open_object]);
            $temp = $statement->fetchColumn();
            if ($temp != '0') {
                $way_back = $temp;
            }

            $query = "SELECT a.resource_id, a.name, a.description
                      FROM resources_objects AS a
                      LEFT JOIN resources_objects AS b ON (b.parent_id = a.resource_id)
                      WHERE a.parent_id = :parent_id AND b.resource_id IS NOT NULL
                      GROUP BY resource_id
                      ORDER BY name";
            $parameters[':parent_id'] = $this->open_object;
        } else {
            $way_back=-1;

            $resRoots = new ResourcesUserRoots();
            $roots = $resRoots->getRoots();

            if (is_array($roots)) {
                $query = "SELECT resource_id, name, description
                          FROM resources_objects
                          WHERE resource_id IN (:resource_ids)
                          ORDER BY name";
                $parameters[':resource_ids'] = $roots;
            } else {
                $query = '';
                $clause = "AND 1=2";
            }
        }

        if ($query) {
            $statement = DBManager::get()->prepare($query);
            $statement->execute($parameters);
            $elements = $statement->fetchAll(PDO::FETCH_ASSOC);

            //check for sublevels in current level
            $sublevels = false;
            if (count($elements)) {
                $ids = array_map(function ($a) { return $a['resource_id']; }, $elements);

                $query = "SELECT 1 FROM resources_objects WHERE parent_id IN (?)";
                $statement = DBManager::get()->prepare($query);
                $statement->execute([$ids]);
                $sublevels = $statement->fetchColumn() > 0;
            }
        }
        ?>
        <tr>
            <td><?= $this->getHistory($this->open_object) ?></td>
            <td width="15%" align="right" nowrap valign="top">
            <? if ($way_back >= 0): ?>
                <a href="<?= URLHelper::getLink('?view=search&quick_view_mode='. $GLOBALS['view_mode']
                            . '&' . (!$way_back ? "reset=TRUE" : "open_level=$way_back")) ?>">
                    <?= Icon::create('arr_2left', 'clickable', ['title' => _('eine Ebene zurück')])->asImg(16, ["class" => 'text-top']) ?>
                </a>
            <? endif; ?>
            </td>
        </tr>
        <tr>
            <td align="left" colspan="2">
            <? if (count($elements) === 0 || !$sublevels): ?>
                <?= MessageBox::info(_("Auf dieser Ebene existieren keine weiteren Unterebenen")) ?>
            <?  else: ?>
                <table width="90%" cellpadding=5 cellspacing=0 border=0 align="center">
                    <?
                    if (count($elements) % 2 == 1)
                        $i=0;
                    else
                        $i=1;
                    print "<td width=\"55%\" valign=\"top\">";
                    foreach ($elements as $element) {
                        if (!$switched && $i > count($elements) / 2) {
                            print "</td><td width=\"40%\" valign=\"top\">";
                            $switched = TRUE;
                        } ?>
                        <a href="<?= URLHelper::getLink('?view=search&quick_view_mode='. $GLOBALS['view_mode'] .'&open_level=' . $element['resource_id']) ?>">
                            <b><?= htmlReady($element['name']) ?></b>
                        </a><br>
                        <? $i++;
                    }
                    ?>
                </table>
            <? endif; ?>
            </td>
        </tr>
        <tr>
            <td align="left" colspan="2">
                <?=_("Ressourcen auf dieser Ebene:")?>
            </td>
        </tr>
        <?
    }

    //private
    function showList() {
        ?>
        <tr>
            <td <? echo ($this->mode == "browse") ? " colspan=\"2\"" : "" ?>>
                <?$result_count=$this->list->showListObjects($this->open_object);
        if (!$result_count) {
            echo MessageBox::info(_("Es existieren keine Einträge auf dieser Ebene.")); ?>
            </td>
        </tr>
            <?
        }
}

    //private
    function showSearchList($check_assigns = FALSE) {
        ?>
        <tr>
            <td <? echo ($this->mode == "browse") ? " colspan=\"2\"" : "" ?>>
                <?$result_count=$this->list->showSearchList($this->searchArray, $check_assigns);
        if (!$result_count) {
            echo MessageBox::info(_("Es wurden keine Einträge zu Ihren Suchkriterien gefunden.")); ?>
            </td>
        </tr>
            <?
        }
    }

    //private
    function showSearch() {
        ?>
        <form class="default" method="post" action="<?= URLHelper::getLink('?search_send=yes&quick_view=search&quick_view_mode='. $GLOBALS['view_mode']) ?>">
            <?= CSRFProtection::tokenTag() ?>
            <? $this->searchFormHeader() ?>
        <? if ($this->check_assigns): ?>
            <? $this->showTimeRange() ?>
        <? endif; ?>
        <? if ($this->mode == 'properties'): ?>
            <? $this->showProperties() ?>
        <? endif; ?>
            <? $this->searchFormFooter() ?>
        </form>

        <br>

        <table border=0 celpadding=2 cellspacing=0 width="99%" align="center">
        <? if ($this->searchArray): ?>
            <? $this->showSearchList($this->check_assigns) ?>
        <? elseif ($this->mode == 'browse'): ?>
            <? $this->browseLevels() ?>
            <? $this->showList() ?>
        <? endif; ?>
        </table>
        <?
    }
}
