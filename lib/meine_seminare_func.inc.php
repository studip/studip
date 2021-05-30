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
        $all_semester = Semester::findAllVisible();
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
        $sem_data = Semester::findAllVisible();
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
        $sem_data = Semester::findAllVisible();
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
