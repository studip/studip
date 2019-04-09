#!/usr/bin/env php
<?php
/**
 * This script converts selected database columns from php serialization to json
 *
 * @author Till Glöggler <studip@tillgloeggler.de>
 * @see    https://develop.studip.de/trac/ticket/7789
 */

require_once __DIR__ . '/studip_cli_env.inc.php';
require_once __DIR__ . '/../config/config_local.inc.php';

ini_set('default_charset', 'utf-8');

function legacy_studip_utf8encode($data)
{
    if (is_array($data)) {
        $new_data = [];
        foreach ($data as $key => $value) {
            $key = legacy_studip_utf8encode($key);
            $new_data[$key] = legacy_studip_utf8encode($value);
        }
        return $new_data;
    }

    if (!preg_match('/[\200-\377]/', $data) && !preg_match("'&#[0-9]+;'", $data)) {
        return $data;
    } else {
        return mb_decode_numericentity(
            mb_convert_encoding($data,'UTF-8', 'WINDOWS-1252'),
            [0x100, 0xffff, 0, 0xffff],
            'UTF-8'
        );
    }
}


function convert_to_json($table, $column, $where = null)
{
    $db = DBManager::get();

    echo "\n\n /*************************************************\n";
    echo " ***** " . $table ." ***** ";
    echo "\n *************************************************/\n\n";

    // get primary keys
    $result = $db->query("SHOW KEYS FROM $table WHERE Key_name = 'PRIMARY'");
    $keys = [];

    while ($data = $result->fetch(PDO::FETCH_ASSOC)) {
        $keys[] = $data['Column_name'];
    }

    // retrieve and convert data
    $result = $db->query("SELECT `". implode('`,`', $keys) ."`, `$column` FROM `$table` WHERE ". ($where ?: '1'));

    while ($data = $result->fetch(PDO::FETCH_ASSOC)) {
        $content = unserialize(legacy_studip_utf8decode($data[$column]));

        if ($content === false) {
            // try to fix string length denotations
            $fixed = preg_replace_callback(
                '/s:([0-9]+):\"(.*?)\";/s',
                function ($matches) { return "s:".strlen($matches[2]).':"'.$matches[2].'";';     },
                $data[$column]
            );

            $content = unserialize(legacy_studip_utf8decode($fixed));
        }

        if ($content !== false) {
            // encode all data
            $json = json_encode(legacy_studip_utf8encode($content), true);

            $query = "UPDATE `$table` SET `$column` = ". $db->quote($json) ."\n WHERE ";

            $where_query = [];
            foreach ($keys as $key) {
                $where_query[] = "`$key` = ". $db->quote($data[$key]);
            }

            $q = $query . implode(' AND ', $where_query);
            $db->exec($q);
            echo $q .";\n";
        } else {
            echo '/* Could not convert: '. print_r($data, 1) ." */\n";
        }
    }
}

convert_to_json('extern_config', 'config');
convert_to_json('aux_lock_rules', 'attributes');
convert_to_json('aux_lock_rules', 'sorting');
convert_to_json('user_config', 'value', "field = 'MY_COURSES_ADMIN_VIEW_FILTER_ARGS'");
convert_to_json('mail_queue_entries', 'mail');
