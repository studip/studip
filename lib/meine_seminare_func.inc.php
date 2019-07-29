<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO

/**
 *
 * @param unknown_type $group_field
 * @param unknown_type $groups
 */
function get_group_names($group_field, $groups)
{
    global $SEM_TYPE, $SEM_CLASS;
    $groupcount = 1;
    if ($group_field == 'sem_tree_id') {
        $the_tree = TreeAbstract::GetInstance("StudipSemTree", ["build_index" => true]);
    }
    if ($group_field == 'sem_number') {
        $all_semester = SemesterData::GetSemesterArray();
    }
    foreach ($groups as $key => $value) {
        switch ($group_field){
            case 'sem_number':
            $ret[$key] = (string) $all_semester[$key]['name'];
            break;

            case 'sem_tree_id':
            if ($the_tree->tree_data[$key]) {
                //$ret[$key] = $the_tree->getShortPath($key);
                $ret[$key][0] = $the_tree->tree_data[$key]['name'];
                $ret[$key][1] = $the_tree->getShortPath($the_tree->tree_data[$key]['parent_id']);
            } else {
                //$ret[$key] = _("keine Studienbereiche eingetragen");
                $ret[$key][0] = _("keine Studienbereiche eingetragen");
                $ret[$key][1] = '';
            }
            break;

            case 'sem_status':
            $ret[$key] = $SEM_TYPE[$key]["name"]." (". $SEM_CLASS[$SEM_TYPE[$key]["class"]]["name"].")";
            break;

            case 'not_grouped':
            $ret[$key] = _("keine Gruppierung");
            break;

            case 'gruppe':
            $ret[$key] = _("Gruppe")." ".$groupcount;
            $groupcount++;
            break;

            case 'dozent_id':
            $ret[$key] = get_fullname($key, 'no_title_short');
            break;

            default:
            $ret[$key] = 'unknown';
            break;
        }
    }
    return $ret;
}

/**
 *
 * @param unknown_type $group_field
 * @param unknown_type $groups
 */
function sort_groups($group_field, &$groups)
{
    switch ($group_field){

        case 'sem_number':
            krsort($groups, SORT_NUMERIC);
        break;

        case 'gruppe':
            ksort($groups, SORT_NUMERIC);
        break;

        case 'sem_tree_id':
            uksort($groups, function ($a, $b) {
                $the_tree = TreeAbstract::GetInstance('StudipSemTree', ['build_index' => true]);
                return $the_tree->tree_data[$a]['index'] - $the_tree->tree_data[$b]['index'];
            });
        break;

        case 'sem_status':
            uksort($groups, function ($a, $b) {
                global $SEM_CLASS,$SEM_TYPE;
                return strnatcasecmp(
                    $SEM_TYPE[$a]['name'] . ' (' . $SEM_CLASS[$SEM_TYPE[$a]['class']]['name'] . ')',
                    $SEM_TYPE[$b]['name'] . ' (' . $SEM_CLASS[$SEM_TYPE[$b]['class']]['name'] . ')'
                );
            });
            break;

        case 'dozent_id':
            uksort($groups, function ($a,$b) {
                $replacements = ['ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue'];
                return strnatcasecmp(
                    str_replace(array_keys($replacements), array_values($replacements), mb_strtolower(get_fullname($a, 'no_title_short'))),
                    str_replace(array_keys($replacements), array_values($replacements), mb_strtolower(get_fullname($b, 'no_title_short')))
                );
            });
            break;

        default:
    }

    foreach ($groups as $key => $value) {
        usort($value, function ($a, $b) {
            if ($a['gruppe'] != $b['gruppe']) {
                return (int)($a['gruppe'] - $b['gruppe']);
            } else {
                if (Config::get()->IMPORTANT_SEMNUMBER) {
                    return strnatcasecmp($a['sem_nr'], $b['sem_nr']);
                } else {
                    return strnatcmp($a['name'], $b['name']);
                }
            }
        });
        $groups[$key] = $value;
    }
    return true;
}

/**
 *
 * @param unknown_type $groups
 * @param unknown_type $my_obj
 */
function correct_group_sem_number(&$groups, &$my_obj)
{
    if (is_array($groups) && is_array($my_obj)) {
        $sem_data = SemesterData::GetSemesterArray();
        //end($sem_data);
        //$max_sem = key($sem_data);
        foreach ($sem_data as $sem_key => $one_sem){
            $current_sem = $sem_key;
            if (!$one_sem['past']) break;
        }
        if (isset($sem_data[$current_sem + 1])){
            $max_sem = $current_sem + 1;
        } else {
            $max_sem = $current_sem;
        }
        foreach ($my_obj as $seminar_id => $values){
            if ($values['obj_type'] == 'sem' && $values['sem_number'] != $values['sem_number_end']){
                if ($values['sem_number_end'] == -1 && $values['sem_number'] < $current_sem) {
                    unset($groups[$values['sem_number']][$seminar_id]);
                    fill_groups($groups, $current_sem, ['seminar_id' => $seminar_id, 'name' => $values['name'], 'gruppe' => $values['gruppe']]);
                    if (!count($groups[$values['sem_number']])) unset($groups[$values['sem_number']]);
                } else {
                    $to_sem = $values['sem_number_end'];
                    for ($i = $values['sem_number']; $i <= $to_sem; ++$i){
                        fill_groups($groups, $i, ['seminar_id' => $seminar_id, 'name' => $values['name'], 'gruppe' => $values['gruppe']]);
                    }
                }
                if ($GLOBALS['user']->cfg->getValue('SHOWSEM_ENABLE')){
                    $sem_name = " (" . $sem_data[$values['sem_number']]['name'] . " - ";
                    $sem_name .= (($values['sem_number_end'] == -1) ? _("unbegrenzt") : $sem_data[$values['sem_number_end']]['name']) . ")";
                    $my_obj[$seminar_id]['name'] .= $sem_name;
                }
            }
        }
        return true;
    }
    return false;
}

/**
 *
 * @param unknown_type $my_obj
 */
function add_sem_name(&$my_obj)
{
    if ($GLOBALS['user']->cfg->getValue('SHOWSEM_ENABLE')) {
        $sem_data = SemesterData::GetSemesterArray();
        if (is_array($my_obj)) {
            foreach ($my_obj as $seminar_id => $values){
                if ($values['obj_type'] == 'sem' && $values['sem_number'] != $values['sem_number_end']){
                    $sem_name = " (" . $sem_data[$values['sem_number']]['name'] . " - ";
                    $sem_name .= (($values['sem_number_end'] == -1) ? _("unbegrenzt") : $sem_data[$values['sem_number_end']]['name']) . ")";
                    $my_obj[$seminar_id]['name'] .= $sem_name;
                } else {
                    $my_obj[$seminar_id]['name'] .= " (" . $sem_data[$values['sem_number']]['name'] . ") ";
                }
            }
        }
    }
    return true;
}

/**
 *
 * @param unknown_type $groups
 * @param unknown_type $group_key
 * @param unknown_type $group_entry
 */
function fill_groups(&$groups, $group_key, $group_entry)
{
    if (is_null($group_key)){
        $group_key = 'not_grouped';
    }
    $group_entry['name'] = str_replace(["ä","ö","ü"], ["ae","oe","ue"], mb_strtolower($group_entry['name']));
    if (!is_array($groups[$group_key]) || (is_array($groups[$group_key]) && !in_array($group_entry, $groups[$group_key]))){
        $groups[$group_key][$group_entry['seminar_id']] = $group_entry;
        return true;
    } else {
        return false;
    }
}

/**
 * This function generates a query to fetch information like (at least)
 * last modification date (last_modified)
 * number of entries      (count)
 * number of new entries  (neue)
 * from object_user_visits for a specific module, denoted by $type.
 * You pass a table-name ($table_name), where the entries for the module are stored,
 * the name of the field working as foreign-key ($range_field), the field to count
 * the number of (new) entries ($count_field).
 * The if-clause ($if_clause) is used to check if there are any new entries at all.
 * With $add_fields you can specify some further fields to be fetched in the query
 * With $add_on you can add some conditions to hold when joining object_user_visits.
 * $object_field is the name of field in object_user_visits to join with
 *
 *
 * @param  string  $table_name    the name of the db-table where the entries for
 *                                the module denoted by $type are stored in
 * @param  string  $range_field   name of the field working as foreign-key, when
 *                                joining object_user_visits
 * @param  string  $count_field   field to count the (new) entries on
 * @param  string  $if_clause     an sql-if-clause, used to check if there are any
 *                                new entries at all
 * @param  string  $type
 * @param  string  $add_fields    some further fields to be fetched in the query
 * @param  string  $add_on        some further conditions to hold when joining object_user_visits
 * @param  string  $object_field  default: my.object_id.
 * @param  string  $user_id       default: current user, user-id of user the query is built for
 * @param  string  $max_field     default: chdate, denotes the field used to find out
 *                                the last_modified-timestamp
 * @return string  the query to operate on objects_user_visits
 */
function get_obj_clause($table_name, $range_field, $count_field, $if_clause,
        $type = false, $add_fields = false, $add_on = false, $object_field = false,
        $user_id = NULL, $max_field = 'chdate')
{
    if (is_null($user_id)) {
        $user_id = $GLOBALS['user']->id;
    }

    $type_sql = ($type) ? "='$type'" : "IN('sem','inst')";
    $object_field = ($object_field) ? $object_field : "my.object_id";
    $on_clause = " ON(my.object_id=a.{$range_field} $add_on) ";
    if (mb_strpos($table_name,'{ON_CLAUSE}') !== false){
        $table_name = str_replace('{ON_CLAUSE}', $on_clause, $table_name);
    } else {
        $table_name .= $on_clause;
    }

    return "SELECT " . ($add_fields ? $add_fields . ", " : "" ) . " my.object_id, COUNT($count_field) as count, COUNT(IF($if_clause, $count_field, NULL)) AS neue,
    MAX(IF($if_clause, $max_field, 0)) AS last_modified
    FROM myobj_{$user_id} my
    INNER JOIN $table_name
    LEFT JOIN object_user_visits b ON (b.object_id = $object_field AND b.user_id = '$user_id' AND b.type $type_sql)
    GROUP BY my.object_id ORDER BY NULL";
}

/**
 *
 * @param array $my_obj : array of seminare-objects with index of seminar_id
 * @param string $user_id
 */
function get_my_obj_values (&$my_obj, $user_id)
{
    $threshold = object_get_visit_threshold();

    $db2 = new DB_seminar;
    $db2->query("CREATE TEMPORARY TABLE IF NOT EXISTS myobj_".$user_id." ( object_id char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL) ENGINE = MEMORY");
    $db2->query("REPLACE INTO  myobj_" . $user_id . " (object_id) VALUES ('" . join("'),('", array_keys($my_obj)) . "')");

    //dokumente
    foreach (array_keys($my_obj) as $obj_id) {
        $readable_folders = null;
        $must_have_perm = $my_obj[$obj_id]['obj_type'] == 'sem' ? 'tutor' : 'autor';
        if (!$GLOBALS['perm']->have_studip_perm($must_have_perm, $obj_id, $user_id)) {
            $readable_folders = array_keys(FileManager::getReadableFolders(Folder::findTopFolder($obj_id)->getTypedFolder(), $user_id));

            if (empty($readable_folders)) {
                continue;
            }
        }

        $db2->query(get_obj_clause('folders a {ON_CLAUSE} INNER JOIN file_refs fr ON (fr.folder_id=a.id)','range_id','fr.id',"(fr.chdate > IFNULL(b.visitdate, $threshold) AND fr.user_id !='$user_id')", 'documents', false, ($readable_folders ? "AND a.id IN('".join("','", $readable_folders)."')" : ""), false, $user_id, 'fr.chdate'));
        while($db2->next_record()) {
            $object_id = $db2->f('object_id');
            if ($my_obj[$object_id]["modules"]["documents"]) {
                $my_obj[$object_id]["neuedokumente"] = $db2->f("neue");
                $my_obj[$object_id]["dokumente"] = $db2->f("count");
                if ($my_obj[$object_id]['last_modified'] < $db2->f('last_modified')){
                    $my_obj[$object_id]['last_modified'] = $db2->f('last_modified');
                }

                $nav = new Navigation('files');

                if ($db2->f('neue')) {
                    $nav->setURL('dispatch.php/course/files/flat');
                    $nav->setImage(Icon::create('files+new', 'attention', ["title" => sprintf(_('%s Dokumente, %s neue'),$db2->f('count'),$db2->f('neue'))]));
                    $nav->setBadgeNumber($db2->f('neue'));
                } else if ($db2->f('count')) {
                    $nav->setURL('dispatch.php/course/files/index');
                    $nav->setImage(Icon::create('files', 'inactive', ["title" => sprintf(_('%s Dokumente'),$db2->f('count'))]));
                }

                $my_obj[$object_id]['files'] = $nav;
            }
        }
    }

    //Ankündigungen
    $db2->query(get_obj_clause('news_range a {ON_CLAUSE} LEFT JOIN news nw ON(a.news_id=nw.news_id AND UNIX_TIMESTAMP() BETWEEN date AND (date+expire))','range_id','nw.news_id',"(chdate > IFNULL(b.visitdate, $threshold) AND nw.user_id !='$user_id')",'news',false,false,'a.news_id', $user_id));
    while($db2->next_record()) {
        $object_id = $db2->f('object_id');
        $my_obj[$object_id]["neuenews"] = $db2->f("neue");
        $my_obj[$object_id]["news"] = $db2->f("count");
        if ($my_obj[$object_id]['last_modified'] < $db2->f('last_modified')){
            $my_obj[$object_id]['last_modified'] = $db2->f('last_modified');
        }

        $nav = new Navigation('news', '');

        if ($db2->f('neue')) {
            $nav->setURL('?new_news=true');
            $nav->setImage(Icon::create('news+new', 'attention', ["title" => sprintf(_('%s Ankündigungen, %s neue'),$db2->f('count'),$db2->f('neue'))]));
            $nav->setBadgeNumber($db2->f('neue'));
        } else if ($db2->f('count')) {
            $nav->setImage(Icon::create('news', 'inactive', ["title" => sprintf(_('%s Ankündigungen'),$db2->f('count'))]));
        }

        $my_obj[$object_id]['news'] = $nav;
    }

    // scm?
    $db2->query(get_obj_clause('scm a','range_id',"IF(content !='',1,0)","(chdate > IFNULL(b.visitdate, $threshold) AND a.user_id !='$user_id')", "scm", 'tab_name', false, false, $user_id));
    while($db2->next_record()) {
        $object_id = $db2->f('object_id');
        if ($my_obj[$object_id]["modules"]["scm"]) {
            $my_obj[$object_id]["neuscmcontent"] = $db2->f("neue");
            $my_obj[$object_id]["scmcontent"] = $db2->f("count");
            $my_obj[$object_id]["scmtabname"] = $db2->f("tab_name");
            if ($my_obj[$object_id]['last_modified'] < $db2->f('last_modified')){
                $my_obj[$object_id]['last_modified'] = $db2->f('last_modified');
            }

            $nav = new Navigation('scm', 'dispatch.php/course/scm');

            if ($db2->f('count')) {
                if ($db2->f('neue')) {
                    $image = Icon::create('infopage+new', 'new');

                    $nav->setBadgeNumber($db2->f('neue'));

                    if ($db2->f('count') == 1) {
                        $title = $db2->f('tab_name')._(' (geändert)');
                    } else {
                        $title = sprintf(_('%s Einträge, %s neue'), $db2->f('count') ,$db2->f('neue'));
                    }
                } else {
                    $image = Icon::create('infopage', 'inactive');

                    if ($db2->f('count') == 1) {
                        $title = $db2->f('tab_name');
                    } else {
                        $title = sprintf(_('%s Einträge'), $db2->f('count'));
                    }
                }

                $nav->setImage($image, ['title' => $title]);
            }

            $my_obj[$object_id]['scm'] = $nav;
        }
    }

    //Termine?
    $db2->query(get_obj_clause('ex_termine a','range_id','termin_id',"(chdate > IFNULL(b.visitdate, $threshold) AND autor_id !='$user_id')", 'schedule', false, " AND a.content <> '' ", false, $user_id));
    while($db2->next_record()) {
        $object_id = $db2->f('object_id');
        if ($my_obj[$object_id]["modules"]["schedule"]) {
            $my_obj[$object_id]["neueausfalltermine"] = $db2->f("neue");
            $my_obj[$object_id]["ausfalltermine"] = $db2->f("count");
            if ($my_obj[$object_id]['last_modified'] < $db2->f('last_modified')){
                $my_obj[$object_id]['last_modified'] = $db2->f('last_modified');
            }
        }
    }
    $db2->query(get_obj_clause('termine a','range_id','termin_id',"(chdate > IFNULL(b.visitdate, $threshold) AND autor_id !='$user_id')", 'schedule', false, false, false, $user_id));
    while($db2->next_record()) {
        $object_id = $db2->f('object_id');
        if ($my_obj[$object_id]["modules"]["schedule"]) {
            $my_obj[$object_id]["neuetermine"] = $db2->f("neue");
            $my_obj[$object_id]["termine"] = $db2->f("count");
            if ($my_obj[$object_id]['last_modified'] < $db2->f('last_modified')){
                $my_obj[$object_id]['last_modified'] = $db2->f('last_modified');
            }

            $nav = new Navigation('schedule', 'dispatch.php/course/dates');
            $neue = $my_obj[$object_id]["neuetermine"] + $my_obj[$object_id]["neueausfalltermine"];
            $count = $my_obj[$object_id]["termine"] + $my_obj[$object_id]["ausfalltermine"];
            if ($neue) {
                $nav->setImage(Icon::create('schedule+new', 'attention', ["title" => sprintf(_('%s Termine, %s neue'),$count,$neue)]));
                $nav->setBadgeNumber($neue);
            } else if ($count) {
                $nav->setImage(Icon::create('schedule', 'inactive', ["title" => sprintf(_('%s Termine'),$count)]));
            }

            $my_obj[$object_id]['schedule'] = $nav;
        }
    }

    //Wiki-Eintraege?
    if (get_config('WIKI_ENABLE')) {
        $db2->query(get_obj_clause('wiki a','range_id','keyword',"(chdate > IFNULL(b.visitdate, $threshold) AND a.user_id !='$user_id')", 'wiki', "COUNT(DISTINCT keyword) as count_d", false, false, $user_id));
        while($db2->next_record()) {
            $object_id = $db2->f('object_id');
            if ($my_obj[$object_id]["modules"]["wiki"]) {
                $my_obj[$object_id]["neuewikiseiten"] = $db2->f("neue");
                $my_obj[$object_id]["wikiseiten"] = $db2->f("count_d");
                if ($my_obj[$object_id]['last_modified'] < $db2->f('last_modified')){
                    $my_obj[$object_id]['last_modified'] = $db2->f('last_modified');
                }

                $nav = new Navigation('wiki');

                if ($db2->f('neue')) {
                    $nav->setURL('wiki.php?view=listnew');
                    $nav->setImage(Icon::create('wiki+new', 'attention', ["title" => sprintf(_('%s WikiSeiten, %s Änderungen'),$db2->f('count_d'),$db2->f('neue'))]));
                    $nav->setBadgeNumber($db2->f('neue'));
                } else if ($db2->f('count')) {
                    $nav->setURL('wiki.php');
                    $nav->setImage(Icon::create('wiki', 'inactive', ["title" => sprintf(_('%s WikiSeiten'),$db2->f('count_d'))]));
                }

                $my_obj[$object_id]['wiki'] = $nav;
            }
        }
    }

    //Lernmodule?
    if (get_config('ELEARNING_INTERFACE_ENABLE')) {
        $db2->query(get_obj_clause('object_contentmodules a','object_id','module_id',"(chdate > IFNULL(b.visitdate, $threshold) AND a.module_type != 'crs')",
                                    'elearning_interface', false , " AND a.module_type != 'crs'", false, $user_id));
//      $db2->query(get_obj_clause('object_contentmodules a','object_id','module_id',"(chdate > IFNULL(b.visitdate, $threshold))", 'elearning_interface'));
        while($db2->next_record()) {
            $object_id = $db2->f('object_id');
            if ($my_obj[$object_id]["modules"]["elearning_interface"]) {
                $my_obj[$object_id]["neuecontentmodule"] = $db2->f("neue");
                $my_obj[$object_id]["contentmodule"] = $db2->f("count");
                if ($my_obj[$object_id]['last_modified'] < $db2->f('last_modified')){
                    $my_obj[$object_id]['last_modified'] = $db2->f('last_modified');
                }

                $nav = new Navigation('elearning', 'elearning_interface.php?view=show');

                if ($db2->f('neue')) {
                    $nav->setImage(Icon::create('learnmodule+new', 'attention', ["title" => sprintf(_('%s Lernmodule, %s neue'),$db2->f('count'),$db2->f('neue'))]));
                    $nav->setBadgeNumber($db2->f('neue'));
                } else if ($db2->f('count')) {
                    $nav->setImage(Icon::create('learnmodule', 'inactive', ["title" => sprintf(_('%s Lernmodule'),$db2->f('count'))]));
                }

                $my_obj[$object_id]['elearning'] = $nav;
            }
        }
    }

    //Umfragen
    if (get_config('VOTE_ENABLE')) {
        $statement = DBManager::get()->prepare("
            SELECT my.object_id,
                   COUNT(questionnaires.questionnaire_id) as count,
                   COUNT(IF(
                        questionnaires.startdate < UNIX_TIMESTAMP()
                        AND (questionnaires.stopdate IS NULL OR questionnaires.stopdate > UNIX_TIMESTAMP())
                        AND questionnaires.chdate >= IFNULL(b.visitdate, :threshold),
                        questionnaires.questionnaire_id,
                        NULL
                   )) AS neue,
                   MAX(IF(
                       questionnaires.startdate < UNIX_TIMESTAMP()
                       AND (questionnaires.stopdate IS NULL OR questionnaires.stopdate > UNIX_TIMESTAMP())
                       AND questionnaires.chdate >= IFNULL(b.visitdate, :threshold),
                       questionnaires.chdate,
                       0
                   )) AS last_modified
            FROM questionnaires
                INNER JOIN questionnaire_assignments USING (questionnaire_id)
                INNER JOIN `myobj_".$user_id."` AS my ON (my.object_id = questionnaire_assignments.range_id AND questionnaire_assignments.range_type = 'course')
                LEFT JOIN object_user_visits b ON (b.object_id = questionnaires.questionnaire_id AND b.user_id = :user_id AND b.type = 'vote')
            WHERE questionnaires.startdate IS NOT NULL AND questionnaires.startdate < UNIX_TIMESTAMP()
                AND (questionnaires.stopdate IS NULL OR questionnaires.stopdate > UNIX_TIMESTAMP())

            GROUP BY my.object_id ORDER BY NULL
        ");
        $statement->execute([
            'user_id' => $user_id,
            'threshold' => $threshold
        ]);

        while($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $object_id = $row['object_id'];
            $my_obj[$object_id]["neuevotes"] = $row["neue"];
            $my_obj[$object_id]["votes"] = $row["count"];
            if ($my_obj[$object_id]['last_modified'] < $row['last_modified']){
                $my_obj[$object_id]['last_modified'] = $row['last_modified'];
            }
        }

        $db2->query(get_obj_clause('eval_range a {ON_CLAUSE} INNER JOIN eval d ON ( a.eval_id = d.eval_id AND d.startdate < UNIX_TIMESTAMP( ) AND (d.stopdate > UNIX_TIMESTAMP( ) OR d.startdate + d.timespan > UNIX_TIMESTAMP( ) OR (d.stopdate IS NULL AND d.timespan IS NULL)))',
                                    'range_id','a.eval_id',"(chdate > IFNULL(b.visitdate, $threshold) AND d.author_id !='$user_id' )",'eval',false,false,'a.eval_id', $user_id));
        while($db2->next_record()) {
            $object_id = $db2->f('object_id');
            $my_obj[$object_id]["neuevotes"] += $db2->f("neue");
            $my_obj[$object_id]["votes"] += $db2->f("count");
            if ($my_obj[$object_id]['last_modified'] < $db2->f('last_modified')){
                $my_obj[$object_id]['last_modified'] = $db2->f('last_modified');
            }
        }

        foreach (array_keys($my_obj) as $object_id) {
            $nav = new Navigation('vote', '#vote');

            if ($my_obj[$object_id]['neuevotes']) {
                $nav->setImage(Icon::create('vote+new', 'attention', ["title" => sprintf(_('%s Fragebögen, %s neue'),$my_obj[$object_id]['votes'],$my_obj[$object_id]['neuevotes'])]));
                $nav->setBadgeNumber($my_obj[$object_id]['neuevotes']);
            } else if ($my_obj[$object_id]['votes']) {
                $nav->setImage(Icon::create('vote', 'inactive', ["title" => sprintf(_('%s Fragebögen'),$my_obj[$object_id]['votes'])]));
            }

            $my_obj[$object_id]['vote'] = $nav;
        }
    }

    //Literaturlisten
    if (get_config('LITERATURE_ENABLE')) {
        $db2->query(get_obj_clause('lit_list a','range_id','list_id',"(chdate > IFNULL(b.visitdate, $threshold) AND a.user_id !='$user_id')", 'literature', false, " AND a.visibility=1", false, $user_id));
        while($db2->next_record()) {
            $object_id = $db2->f('object_id');
            if ($my_obj[$object_id]["modules"]["literature"]) {
                $my_obj[$object_id]["neuelitlist"] = $db2->f("neue");
                $my_obj[$object_id]["litlist"] = $db2->f("count");
                if ($my_obj[$object_id]['last_modified'] < $db2->f('last_modified')){
                    $my_obj[$object_id]['last_modified'] = $db2->f('last_modified');
                }

                $nav = new Navigation('literature', 'dispatch.php/course/literature');

                if ($db2->f('neue')) {
                    $nav->setImage(Icon::create('literature+new', 'attention', ["title" => sprintf(_('%s Literaturlisten, %s neue'),$db2->f('count'),$db2->f('neue'))]));
                    $nav->setBadgeNumber($db2->f('neue'));
                } else if ($db2->f('count')) {
                    $nav->setImage(Icon::create('literature', 'inactive', ["title" => sprintf(_('%s Literaturlisten'),$db2->f('count'))]));
                }

                $my_obj[$object_id]['literature'] = $nav;
            }
        }
    }

    // TeilnehmerInnen
    if ($GLOBALS['perm']->have_perm('tutor')) {
        //vorläufige Teilnahme
        $db2->query(get_obj_clause('admission_seminar_user a','seminar_id','a.user_id',
            "(mkdate > IFNULL(b.visitdate, $threshold) AND a.user_id !='$user_id')",
            'participants', false, " AND a.status='accepted' ", false, $user_id, 'mkdate'));

        while($db2->next_record()) {
            $object_id = $db2->f('object_id');
            if ($my_obj[$object_id]["modules"]["participants"]) {
                if ($GLOBALS['perm']->have_perm('admin', $user_id) || in_array($my_obj[$object_id]['status'], words('dozent tutor'))) {
                    $my_obj[$object_id]["new_accepted_participants"] = $db2->f("neue");
                    $my_obj[$object_id]["count_accepted_participants"] = $db2->f("count");
                    if ($my_obj[$object_id]['last_modified'] < $db2->f('last_modified')){
                        $my_obj[$object_id]['last_modified'] = $db2->f('last_modified');
                    }
                }
            }
        }

        $db2->query(get_obj_clause('seminar_user a','seminar_id','a.user_id',
            "(mkdate > IFNULL(b.visitdate, $threshold) AND a.user_id !='$user_id')",
            'participants', false, false, false, $user_id, 'mkdate'));

        $all_auto_inserts = AutoInsert::getAllSeminars(true);
        $auto_insert_perm = Config::get()->AUTO_INSERT_SEM_PARTICIPANTS_VIEW_PERM;

        while($db2->next_record()) {
            $object_id = $db2->f('object_id');
            // show the participants-icon only if the module is activated and it is not an auto-insert-sem
            if ($my_obj[$object_id]["modules"]["participants"]) {
                if (in_array($object_id, $all_auto_inserts)) {
                    if ($GLOBALS['perm']->have_perm('admin', $user_id)
                    && !$GLOBALS['perm']->have_perm($auto_insert_perm, $user_id)) {
                        continue;
                    } else if ($GLOBALS['perm']->permissions[$auto_insert_perm]  > $GLOBALS['perm']->permissions[$my_obj[$object_id]['status']]) {
                        continue;
                    }
                }
                $my_obj[$object_id]["newparticipants"] = $db2->f("neue");
                $my_obj[$object_id]["countparticipants"] = $db2->f("count");
                if ($GLOBALS['perm']->have_perm('admin', $user_id) || in_array($my_obj[$object_id]['status'], words('dozent tutor'))) {
                    if ($my_obj[$object_id]['last_modified'] < $db2->f('last_modified')){
                        $my_obj[$object_id]['last_modified'] = $db2->f('last_modified');
                    }
                }
                if (SeminarCategories::GetByTypeId($my_obj[$object_id]['sem_status'])->studygroup_mode) {
                    $nav = new Navigation('participants', 'dispatch.php/course/studygroup/members/?cid='. $object_id);
                } else {
                    $nav = new Navigation('participants', 'dispatch.php/course/members/index');
                }
                $neue = $my_obj[$object_id]["newparticipants"] + $my_obj[$object_id]["new_accepted_participants"];
                $count = $my_obj[$object_id]["countparticipants"] + $my_obj[$object_id]["count_accepted_participants"];
                if ($neue && ($GLOBALS['perm']->have_perm('admin', $user_id) || in_array($my_obj[$object_id]['status'], words('dozent tutor')))) {
                    $nav->setImage(Icon::create('persons+new', 'attention', ["title" => sprintf(_('%s Teilnehmende, %s neue'),$count,$neue)]));
                    $nav->setBadgeNumber($neue);
                } else if ($count) {
                    $nav->setImage(Icon::create('persons', 'inactive', ["title" => sprintf(_('%s Teilnehmende'),$count)]));
                }
                $my_obj[$object_id]['participants'] = $nav;
            }
        }
    } else {    // show only the participants-icon, no colouring!
        foreach ($my_obj as $object_id => $data) {
            $all_auto_inserts = AutoInsert::getAllSeminars(true);
            $auto_insert_perm = Config::get()->AUTO_INSERT_SEM_PARTICIPANTS_VIEW_PERM;

            if (in_array($object_id, $all_auto_inserts)) {
                if ($GLOBALS['perm']->have_perm('admin', $user_id)
                && !$GLOBALS['perm']->have_perm($auto_insert_perm, $user_id)) {
                    continue;
                } else if ($GLOBALS['perm']->permissions[$auto_insert_perm]  > $GLOBALS['perm']->permissions[$my_obj[$object_id]['status']]) {
                    continue;
                }
            }

            if ($my_obj[$object_id]["modules"]["participants"]) {
                if (SeminarCategories::GetByTypeId($my_obj[$object_id]['sem_status'])->studygroup_mode) {
                    $nav = new Navigation('participants', 'dispatch.php/course/studygroup/members/?cid='. $object_id);
                } else {
                    $nav = new Navigation('participants', 'dispatch.php/course/members/index');
                }
                $nav->setImage(Icon::create('persons', 'inactive', ["title" => _('Teilnehmende')]));
                $my_obj[$object_id]['participants'] = $nav;
            }
        }
    }

    $db2->query("DROP TABLE IF EXISTS myobj_" . $user_id);
    return;
}

/**
 * This function returns all valid fields that may be used for course
 * grouping in "My Courses".
 *
 * @return array All fields that may be specified for course grouping
 */
function getValidGroupingFields()
{
    return [
        'not_grouped',
        'sem_number',
        'sem_tree_id',
        'sem_status',
        'gruppe',
        'dozent_id'
    ];
}
