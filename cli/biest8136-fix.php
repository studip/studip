#!/usr/bin/env php
<?php
/**
 * This script adjusts all activities so that anonymous posts will actually be
 * anonymous.
 *
 * @author Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @see    https://develop.studip.de/trac/ticket/8136
 */

require_once __DIR__ . '/studip_cli_env.inc.php';
require_once __DIR__ . '/../config/config_local.inc.php';

$query = "UPDATE `activities`
          SET `actor_type` = 'anonymous',
              `actor_id` = ''
          WHERE `provider` = :provider
            AND `actor_type` != 'anonymous'
            AND `object_id` IN (
                SELECT `topic_id`
                FROM `forum_entries`
                WHERE `anonymous` != 0
            )";
$statement = DBManager::get()->prepare($query);
$statement->bindValue(':provider', 'Studip\\Activity\\ForumProvider');
$statement->execute();

printf(
    "%u forum post activities were anonymized\n",
    $statement->rowCount()
);
