<?php
# Lifter001: TEST
# Lifter007: TODO
# Lifter010: TODO
/**
 * browse.php - Personensuche in Stud.IP
 *
 * @author      Stefan Suchi <suchi@gmx.de>
 * @author      Michael Riehemann <michael.riehemann@uni-oldenburg.de>
 * @license     http://www.gnu.org/licenses/gpl.html GPL Licence 2
 * @package     studip_core
 */


require '../lib/bootstrap.php';

page_open(array(
    'sess' => 'Seminar_Session',
    'auth' => 'Seminar_Default_Auth',
    'perm' => 'Seminar_Perm',
    'user' => 'Seminar_User'
));
$perm->check('user');

require_once 'lib/seminar_open.php';
require_once 'lib/statusgruppe.inc.php';
require_once 'lib/user_visible.inc.php';

//Basics
PageLayout::setHelpKeyword('Basis.SuchenPersonen');
PageLayout::setTitle(_('Suche nach Personen'));
Navigation::activateItem('/search/users');

$template = $GLOBALS['template_factory']->open('browse');
$template->set_layout('layouts/base');

/* --- Actions -------------------------------------------------------------- */

if (!Request::submitted('reset')) {
    $name = Request::get('name_parameter');
    $inst_id = Request::option('inst_id');
    $sem_id = Request::option('sem_id');
}

//Eine Suche wurde abgeschickt

// Suchstring merken für evtl. Sortieraktionen
if (Request::get('name') && !Request::submitted('send')) {
    $name = Request::get('name');
}

if (isset($name)) {
    $template->set_attribute('name', $name);
    $template->set_attribute('inst_id', $inst_id);
    $template->set_attribute('sem_id', $sem_id);
}

//Ergebnisse sollen sortiert werden
$sortby_fields = array('perms', 'status');
$sortby = Request::option('sortby');
$sortby = in_array($sortby, $sortby_fields) ? "$sortby, Nachname, Vorname" : 'Nachname, Vorname';

// exclude AUTO_INSERT_SEM courses
$exclude_sem = "AND Seminar_id NOT IN (SELECT seminar_id FROM auto_insert_sem)";

//List of Institutes
$parameters = array();
if ($perm->have_perm('admin')) {
    $query = "SELECT Institut_id, Name
              FROM Institute
              WHERE (Institute.modules & 16)
              ORDER BY name";
} else {
    $query = "SELECT Institut_id, Name
              FROM user_inst
              LEFT JOIN Institute USING (institut_id)
              WHERE user_id = ? AND (Institute.modules & 16)
              ORDER BY name";
    $parameters[] = $user->id;
}
$statement = DBManager::get()->prepare($query);
$statement->execute($parameters);

while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
    $institutes[] = array(
        'id'   => $row['Institut_id'],
        'name' => my_substr($row['Name'], 0, 40)
    );
}

//List of Seminars
if (!$perm->have_perm('admin')) {
    $all_semes = array_reverse(SemesterData::GetSemesterArray());


    $query = "SELECT Seminar_id, Name
              FROM seminar_user
              LEFT JOIN seminare USING (Seminar_id)
              WHERE user_id = ? AND (seminare.modules & 8) {$exclude_sem}
                AND (start_time <= ? AND (? <= (start_time + duration_time) OR duration_time = -1))
              ORDER BY start_time DESC";
    $statement = DBManager::get()->prepare($query);
    foreach($all_semes as $sem) {
        if(!$sem['semester_id']) {
            continue;
        }
        $statement->execute(array($user->id, $sem['beginn'], $sem['beginn']));

        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $courses[$sem['name']][] = array(
                'id'   => $row['Seminar_id'],
                'name' => my_substr($row['Name'], 0, 40)
            );
        }
    }
}

$template->set_attribute('institutes', $institutes);
$template->set_attribute('courses', $courses);

$vis_query = get_vis_query('auth_user_md5', 'search') . ' AS visible';

// quick search
$sql = "SELECT username, CONCAT(Vorname, ' ', Nachname, ' (', username, ')'),
               CONCAT(Vorname, ' ', Nachname), {$vis_query}
        FROM auth_user_md5
        LEFT JOIN user_visibility USING (user_id)
        WHERE CONCAT(Vorname, ' ', Nachname) LIKE :input
          OR CONCAT(Nachname, ' ', Vorname) LIKE :input
          OR CONCAT(Nachname, ', ', Vorname) LIKE :input
          OR username LIKE :input
        HAVING visible = 1
        ORDER BY Nachname, Vorname";
$search_object = new SQLSearch($sql, _('Vorname, Nachname oder Nutzername'), 'username');

$template->set_attribute('search_object', $search_object);

/* --- Results -------------------------------------------------------------- */

$fields = array($_fullname_sql['full_rev'].' AS fullname', 'username', 'perms', 'auth_user_md5.user_id', $vis_query);
$tables = array('auth_user_md5', 'LEFT JOIN user_info USING (user_id)', 'LEFT JOIN user_visibility USING (user_id)');
$parameters = array();

if ($inst_id) {
    $query = "SELECT 1 FROM user_inst WHERE Institut_id = ? AND user_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute(array($inst_id, $user->id));
    $check = $statement->fetchColumn();

    // entweder wir gehoeren auch zum Institut oder sind global admin
    if ($check || $perm->have_perm('admin')) {
        $fields[] = 'user_inst.inst_perms';
        $tables[] = 'JOIN user_inst USING (user_id)';
        $filter[] = "user_inst.Institut_id = :inst_id";
        $filter[] = "user_inst.inst_perms != 'user'";

        $parameters[':inst_id'] = $inst_id;
    }
}

if ($sem_id) {
    $query = "SELECT 1 FROM seminar_user WHERE Seminar_id = ? AND user_id = ? {$exclude_sem}";
    $statement = DBManager::get()->prepare($query);
    $statement->execute(array($sem_id, $user->id));
    $check = $statement->fetchColumn();

    // wir gehoeren auch zum Seminar
    if ($check) {
        $fields[] = 'seminar_user.status';
        $tables[] = 'JOIN seminar_user USING (user_id)';
        $filter[] = "seminar_user.Seminar_id = :sem_id";

        $parameters[':sem_id'] =  $sem_id;
    }
}

// freie Suche
if (mb_strlen($name) > 2) {
    $name = str_replace('%', '\%', $name);
    $name = str_replace('_', '\_', $name);
    $filter[] = "(CONCAT(Vorname, ' ', Nachname) LIKE :needle OR CONCAT(Nachname, ' ', Vorname) LIKE :needle OR CONCAT(Nachname, ', ', Vorname) LIKE :needle OR username LIKE :needle)";
    $parameters[':needle'] = '%' . $name . '%';
}

if ($filter && count($filter)) {
    $_fields  = implode(', ', $fields);
    $_tables  = implode(' ', $tables);
    $_filters = implode(' AND ', $filter);

    $query = "SELECT {$_fields}
              FROM {$_tables}
              WHERE {$_filters}
              ORDER BY {$sortby}";
    $statement = DBManager::get()->prepare($query);
    $statement->execute($parameters);

    while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
        if (!$row['visible']) {
            continue;
        }

        $userinfo = array(
            'user_id'  => $row['user_id'],
            'username' => $row['username'],
            'fullname' => $row['fullname'],
            'status'   => $row['status'] ?: $row['perms'],
        );

        if (isset($row['inst_perms'])) {
            $groups = [];
            Statusgruppen::findEachBySql(function ($group) use ($row, &$groups) {
                $groups = array_merge(
                    $groups,
                    $group->getFullGenderedPaths($row['user_id'])
                );
            }, 'range_id = ?', [$inst_id]);
            $userinfo['status'] = implode(', ', $groups) ?: _('keiner Funktion zugeordnet');
        }

        $users[] = $userinfo;
    }

    $template->set_attribute('users', $users);
}

/* --- View ----------------------------------------------------------------- */

echo $template->render();
page_close();
