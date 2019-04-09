<?php
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
//
// Copyright (C) 2014 Arne Schröder <schroeder@data-quest>,
// Suchi & Berg GmbH <info@data-quest.de>
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
require_once 'lib/object.inc.php';

/**
 * HelpTour.class.php - model class for Stud.IP tours
 *
 *
 *
 *
 * @author   Arne Schröder <schroeder@data-quest>
 * @access   public
 *
 * @property string tour_id database column
 * @property string id alias column for tour_id
 * @property string name database column
 * @property string description database column
 * @property string type database column
 * @property string roles database column
 * @property string version database column
 * @property string language database column
 * @property string studip_version database column
 * @property string installation_id database column
 * @property string mkdate database column
 * @property SimpleORMapCollection steps has_many HelpTourStep
 * @property SimpleORMapCollection audiences has_many HelpTourAudience
 * @property HelpTourSettings settings has_one HelpTourSettings
 */
class HelpTour extends SimpleORMap
{
    /**
     *
     */
    protected static function configure($config = [])
    {
        $config['db_table'] = 'help_tours';
        $config['has_one']['settings'] = [
            'class_name'        => 'HelpTourSettings',
            'assoc_foreign_key' => 'tour_id',
            'on_delete'         => 'delete',
            'on_store'          => 'store',
        ];
        $config['has_many']['steps'] = [
            'class_name'        => 'HelpTourStep',
            'assoc_foreign_key' => 'tour_id',
            'on_delete'         => 'delete',
            'on_store'          => 'store',
        ];
        $config['has_many']['audiences'] = [
            'class_name'        => 'HelpTourAudience',
            'assoc_foreign_key' => 'tour_id',
            'on_delete'         => 'delete',
            'on_store'          => 'store',
        ];

        parent::configure($config);
    }

    /**
     * get visible tours for helpbar
     *
     * @return array                  set of tours
     */
    public static function GetHelpbarTourData()
    {
        $visible_tours = [];
        $route = get_route();
        $tours = HelpTour::getToursByRoute($route);
        foreach($tours as $index => $tour) {
            if ($tour->isVisible() && ($tour->settings->access !== 'link')) {
                $visible_tours[$index] = $tour;
                if (in_array($tour->settings->access, ['autostart', 'autostart_once']) && !$GLOBALS['user']->cfg->TOUR_AUTOSTART_DISABLE) {
                    $user_visit = new HelpTourUser([$tour->tour_id, $GLOBALS['user']->user_id]);
                    if ($tour->settings->access === 'autostart_once' && $user_visit->isNew()) {
                        $active_tour_id = $tour->tour_id;
                        $active_tour_step_nr = 1;
                    } elseif ($tour->settings->access === 'autostart' && !$user_visit->completed) {
                        $active_tour_id = $tour->tour_id;
                        $active_tour_step_nr = 1;
                    }
                }
            }
        }
        //if there is an active tour, initialize it
        if ($_SESSION['active_tour']['tour_id']
            && ($_SESSION['active_tour']['last_route'] === $route
                || $_SESSION['active_tour']['next_route'] === $route))
        {
            $active_tour = new HelpTour($_SESSION['active_tour']['tour_id']);
            $step_nr = $_SESSION['active_tour']['step_nr'];
            if ($_SESSION['active_tour']['last_route'] !== $route && $_SESSION['active_tour']['next_route'] === $route) {
                while ($_SESSION['active_tour']['last_route'] === $active_tour->steps[$step_nr - 1]->route) {
                    $step_nr += 1;
                }
            }
            if ($route === $active_tour->steps[$step_nr - 1]->route) {
                $_SESSION['active_tour']['step_nr'] = $step_nr;
                $active_tour_id = $_SESSION['active_tour']['tour_id'];
                $active_tour_step_nr = $step_nr;
            }
        }
        return [
            'tours'               => $visible_tours,
            'active_tour_id'      => $active_tour_id,
            'active_tour_step_nr' => $active_tour_step_nr,
        ];
    }

    /**
     * fetches set of tours from database for given route
     *
     * @param string $route           route for tours to begin
     * @param boolean $as_objects     include HelpTour objects in result array
     * @return array                  set of tours
     */
    public static function GetToursByRoute($route = '')
    {
        if (!$route) {
            $route = get_route();
        }
        $query = "SELECT tour_id AS idx, help_tours.*
                  FROM help_tour_steps
                  INNER JOIN help_tours USING (tour_id)
                  WHERE route = ? AND step = 1
                  ORDER BY name ASC";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$route]);
        $ret = $statement->fetchGrouped(PDO::FETCH_ASSOC);

        return HelpTour::GetTourObjects($ret);
    }

    /**
     * fetches set of tours from database filtered by parameters
     *
     * @param string $term            search term for tour name
     * @param boolean $as_objects     include HelpTour objects in result array
     * @return array                  set of tours
     */
    public static function GetToursByFilter($term = '')
    {
        $params = [];
        $condition = '';
        if (mb_strlen(trim($term)) >= 3) {
            $condition = "WHERE name LIKE CONCAT('%', ?, '%')";
            $params[] = $term;
        }
        $query = "SELECT tour_id AS idx, help_tours.*
                  FROM help_tours
                  {$condition}
                  ORDER BY name ASC";
        $statement = DBManager::get()->prepare($query);
        $statement->execute($params);
        $ret = $statement->fetchGrouped(PDO::FETCH_ASSOC);

        return HelpTour::GetTourObjects($ret);
    }

    /**
     * fetches tour conflicts
     *
     * @return array                  set of tour objects
     */
    public static function GetConflicts()
    {
        $conflicts = [];
        $query = "SELECT tour_id AS idx, help_tours.*
                  FROM help_tours
                  WHERE installation_id = ?
                  ORDER BY name ASC";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([Config::get()->STUDIP_INSTALLATION_ID]);
        $ret = $statement->fetchGrouped(PDO::FETCH_ASSOC);
        foreach ($ret as $index => $data) {
            $query = "SELECT tour_id AS idx, help_tours.*
                      FROM help_tours
                      WHERE global_tour_id = ?
                        AND language = ?
                        AND studip_version >= ?
                        AND installation_id <> ?
                      ORDER BY studip_version DESC
                      LIMIT 1";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([
                $data['global_tour_id'],
                $data['language'],
                $data['studip_version'],
                Config::get()->STUDIP_INSTALLATION_ID,
            ]);
            $ret2 = $statement->fetchGrouped(PDO::FETCH_ASSOC);
            if (count($ret2) > 0) {
                $conflicts[] = HelpTour::GetTourObjects(array_merge([$index => $data], $ret2));
            }
        }
        return $conflicts;
    }

    /**
     * builds tour objects for given set of tour data
     *
     * @param array $tour_result      tour set
     * @return array                  set of tour objects
     */
    public static function GetTourObjects($tour_result)
    {
        $objects = [];
        if (is_array($tour_result)){
            foreach($tour_result as $id => $result){
                $objects[$id] = new HelpTour();
                $objects[$id]->setData($result, true);
                $objects[$id]->setNew(false);
            }
        }
        return $objects;
    }

    /**
     * checks if tour is visible for current user
     */
    public function isVisible()
    {
        if (!$this->settings->active) {
            return false;
        }

        $language = mb_substr($GLOBALS['user']->preferred_language, 0, 2);
        if (!$language) {
            $language = 'de';
        }
        if ($language !== $this->language) {
            return false;
        }

        $current_role = User::findCurrent() ? User::findCurrent()->perms : 'nobody';
        if (mb_strpos($this->roles, $current_role) === false) {
            return false;
        }
        foreach ($this->audiences as $audience) {
            switch ($audience->type) {
                case 'inst':
                    $table_name = 'user_inst';
                    $field_name = 'Institut_id';
                break;
                case 'sem':
                    $table_name = 'seminar_user';
                    $field_name = 'Seminar_id';
                break;
                case 'studiengang':
                    $table_name = 'user_studiengang';
                    $field_name = 'fach_id';
                break;
                case 'abschluss':
                    $table_name = 'user_studiengang';
                    $field_name = 'abschluss_id';
                break;
                case 'userdomain':
                    $table_name = 'user_userdomains';
                    $field_name = 'userdomain_id';
                break;
            }
            if ($audience->range_id && $table_name) {
                $query = "SELECT *
                          FROM {$table_name}
                          WHERE user_id = ?
                            AND {$field_name} = ?";
                $items = [$GLOBALS['user']->user_id, $audience->range_id];
                $statement = DBManager::get()->prepare($query);
                $statement->execute($items);
                $ret = $statement->fetchOne(PDO::FETCH_ASSOC);
                if (!count($ret)) {
                    return false;
                }
            } elseif ($table_name) {
                $query = "SELECT * FROM {$table_name} WHERE user_id = ?";
                $items = [$GLOBALS['user']->user_id];
                $statement = DBManager::get()->prepare($query);
                $statement->execute($items);
                $ret = $statement->fetchOne(PDO::FETCH_ASSOC);
                if (count($ret)) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * adds step to the tour and rearranges existing steps
     */
    public function addStep($data, $position = 0)
    {
        $step = new HelpTourStep();
        $step->setData($data, true);
        if ($position) {
            $step->step = $position;
        } else {
            $step->step = count($this->steps) + 1;
        }
        $step->tour_id = $this->tour_id;
        if ($step->validate()) {
            if ($position && $position <= count($this->steps)) {
                $query = "UPDATE help_tour_steps
                          SET step = step + 1
                          WHERE tour_id = ? AND step >= ?
                          ORDER BY step DESC";
                $statement = DBManager::get()->prepare($query);
                $statement->execute([$this->tour_id, $position]);
            }
            $step->store();
            $this->restore();
            return true;
        }

        return false;
    }

    /**
     * deletes step and rearranges existing steps
     */
    public function deleteStep($position = 0)
    {
        if (!$position || count($this->steps) < 2) {
            PageLayout::postError(_('Löschen nicht möglich. Die Tour muss mindestens einen Schritt enthalten.'));
            return false;
        }
        $query = "DELETE FROM help_tour_steps
                  WHERE tour_id = ? AND step = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$this->tour_id, $position]);

        $query = "UPDATE help_tour_steps
                  SET step = step - 1
                  WHERE tour_id = ? AND step > ?
                  ORDER BY step ASC";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$this->tour_id, $position]);
        $this->restore();
        return true;
    }

    /**
     * checks, if basic tour data is complete
     *
     * @return boolean true or false
     */
    public function validate()
    {
        if (!$this->name || !$this->description) {
            PageLayout::postError(_('Die Tour muss einen Namen und eine Beschreibung haben.'));
            return false;
        }
        if (!$this->type) {
            PageLayout::postError(_('Ungültige oder fehlende Angabe zur Art der Tour.'));
            return false;
        }
        if (!$this->roles) {
            PageLayout::postError(_('Angabe des Nutzendenstatus fehlt.'));
            return false;
        }
        if (!$this->version) {
            $this->version = 1;
        }
        if (!$this->isNew() && count($this->steps) === 0) {
            PageLayout::postError(_('Die Tour muss mindestens einen Schritt enthalten.'));
            return false;
        }
        return true;
    }
}
