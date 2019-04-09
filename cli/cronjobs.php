#!/usr/bin/env php
<?php
/**
* cronjobs - Helper script for the cronjobs
*
* @author Jan-Hendrik Willms <tleilax+studip@gmail.com>
* @license http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
* @category Stud.IP
* @since 3.1
* @todo Parameter handling!
*/

require_once 'studip_cli_env.inc.php';

$argc = $_SERVER['argc'];
$argv = $_SERVER['argv'];

$opts = getopt('hl', ['help', 'list']);

if (isset($opts['l']) || isset($opts['list'])) {
    $tasks = CronjobTask::findBySql('1');
    foreach ($tasks as $task) {
        $description = call_user_func([$task->class, 'getDescription']);
        fwrite(STDOUT, sprintf('%s %s' . PHP_EOL, $task->id, $description));
    }
    exit(0);
}

if ($argc < 2 || isset($opts['h']) || isset($opts['help'])) {
    fwrite(STDOUT,'Usage: ' . basename(__FILE__) . ' [--help] [--list] <task_id> [last_result]' . PHP_EOL);
    exit(0);
}


$id = $_SERVER['argv'][1];
$last_result = $argc > 2 ? $_SERVER['argv'][2] : null;
$task = CronjobTask::find($id);
if (!$task) {
    fwrite(STDOUT, 'Unknown task id' . PHP_EOL);
    exit(0);
}

if (!file_exists($GLOBALS['STUDIP_BASE_PATH'] . '/' . $task->filename)) {
    fwrite(STDOUT, 'Invalid task, unknown filename "' . $task->filename . '"' . PHP_EOL);
    exit(0);
}

require_once $task->filename;
if (!class_exists($task->class)) {
    fwrite(STDOUT, 'Invalid task, unknown class "' . $task->class . '"' . PHP_EOL);
    exit(0);
}

$task->engage($last_result);
