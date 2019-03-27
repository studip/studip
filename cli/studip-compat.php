#!/usr/bin/env php
<?php
require_once 'studip_cli_env.inc.php';

$opts = getopt('fhnoc', ['filenames', 'help', 'non-recursive', 'verbose', 'no-color']);

if (isset($opts['h']) || isset($opts['help'])) {
    fwrite(STDOUT, 'Stud.IP compatibility scanner - Checks plugins for common issues' . PHP_EOL);
    fwrite(STDOUT, '================================================================' . PHP_EOL);
    fwrite(STDOUT, 'Usage: ' . basename(__FILE__) . ' [OPTION] [VERSION] [FOLDER] ..' . PHP_EOL);
    fwrite(STDOUT, PHP_EOL);
    fwrite(STDOUT, '[VERSION] is optional, if not given all checks are applied.' . PHP_EOL);
    fwrite(STDOUT, '[FOLDER] will default to the plugins_packages folder.' . PHP_EOL);
    fwrite(STDOUT, 'Supply as many folders as you need.' . PHP_EOL);
    fwrite(STDOUT, PHP_EOL);
    fwrite(STDOUT, 'Options:' . PHP_EOL);
    fwrite(STDOUT, ' -h, --help            Display this help' . PHP_EOL);
    fwrite(STDOUT, ' -f, --filenames       Display only filenames' . PHP_EOL);
    fwrite(STDOUT, ' -n, --non-recursive   Do not scan recursively into subfolders' . PHP_EOL);
    fwrite(STDOUT, ' -c, --no-color        Do not use colors for output' . PHP_EOL);
    fwrite(STDOUT, ' -v, --verbose         Print additional information' . PHP_EOL);
    fwrite(STDOUT, PHP_EOL);
    exit(0);
}

// Reduce arguments by options (this is far from perfect)
$args = $_SERVER['argv'];
$arg_stop = array_search('--', $args);
if ($arg_stop !== false) {
    $args = array_slice($args, $arg_stop + 1);
} elseif (count($opts)) {
    $args = array_slice($args, 1 + count($opts));
} else {
    $args = array_slice($args, 1);
}

$verbose        = isset($opts['v']) || isset($opts['verbose']);
$only_filenames = isset($opts['f']) || isset($opts['filenames']);
$recursive      = !(isset($opts['n']) || isset($opts['non-recursive']));
$no_colors      = isset($opts['c']) || isset($opts['no-color']);
$version        = null;
$folders        = array_values($args) ?: [];

if (count($folders) > 0 && preg_match('/^\d+\.\d+$/', $folders[0])) {
    $version = array_shift($folders);
}

// Prepare logging mechanism
$log = function ($message) use ($no_colors) {
    $ansi = array(
        'off'        => 0,
        'bold'       => 1,
        'italic'     => 3,
        'underline'  => 4,
        'blink'      => 5,
        'inverse'    => 7,
        'hidden'     => 8,
        'black'      => 30,
        'red'        => 31,
        'green'      => 32,
        'yellow'     => 33,
        'blue'       => 34,
        'magenta'    => 35,
        'cyan'       => 36,
        'white'      => 37,
        'black_bg'   => 40,
        'red_bg'     => 41,
        'green_bg'   => 42,
        'yellow_bg'  => 43,
        'blue_bg'    => 44,
        'magenta_bg' => 45,
        'cyan_bg'    => 46,
        'white_bg'   => 47
    );

    $message = trim($message);

    if ($message) {
        $args = array_slice(func_get_args(), 1);
        $message = vsprintf($message . "\n", $args);

        $ansi_codes = implode('|', array_keys($ansi));
        if (preg_match_all('/#\{((?:(?:' . $ansi_codes . '),?)+):(.+?)\}/s', $message, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $chunk = '';
                if (!$no_colors) {
                    $codes = explode(',', $match[1]);
                    foreach ($codes as $code) {
                        $chunk .= "\033[{$ansi[$code]}m";
                    }
                }
                $chunk .= $match[2];
                if (!$no_colors) {
                    $chunk .= "\033[{$ansi[off]}m";
                }

                $message = str_replace($match[0], $chunk, $message);
            }
        }

        print $message;
    }
};
$log_if = function ($condition, $message) use ($log) {
    if ($condition) {
        call_user_func_array($log, array_slice(func_get_args(), 1));
    }
};

// Reduces filename by base path and plugin folder
$reduce = function ($folder) {
    $folder = str_replace($GLOBALS['STUDIP_BASE_PATH'] . '/', '', $folder);
    $folder = str_replace('public/plugins_packages/', '', $folder);
    return $folder;
};

// Get rules
if (!$version) {
    $rules = [];
    foreach (glob(__DIR__ . '/compatbility-rules/*.php') as $file) {
        $version_rules = require $file;
        $rules = array_merge($rules, $version_rules);
    }
} elseif (!file_exists(__DIR__ . "/compatibility-rules/studip-{$version}.php")) {
    $log('#{red:No rules defined for Stud.IP version %s}', $version);
    die;
} else {
    $rules = require __DIR__ . "/compatibility-rules/studip-{$version}.php";
}

// Prepare folders
if (count($folders) === 0) {
    $folders = rtrim($GLOBALS['STUDIP_BASE_PATH'], '/') . '/public/plugins_packages';
    $folders = glob($folders . '/*/*');
}
$folders = array_unique($folders);

// Main checker
$check = function ($filename) use ($rules) {
    $errors = [];

    $contents = strtolower(file_get_contents($filename));
    foreach ($rules as $needle => $suggestion) {
        if (strpos($contents, strtolower($needle)) > 0) {
            $errors[$needle] = $suggestion;
        }
    }
    return $errors;
};

// Engage
foreach ($folders as $folder) {
    if (!file_exists($folder) || !is_dir($folder)) {
        $log_if($verbose, 'Skipping non-folder arg #{red:%s}', $folder);
        continue;
    }

    $log_if($verbose && !$only_filenames, '#{green:Scanning} %s', $reduce($folder));
    if ($recursive) {
        $iterator = new RecursiveDirectoryIterator($folder, FilesystemIterator::FOLLOW_SYMLINKS | FilesystemIterator::UNIX_PATHS);
        $iterator = new RecursiveIteratorIterator($iterator);
    } else {
        $iterator = new DirectoryIterator($folder);
    }
    $regexp_iterator = new RegexIterator($iterator, '/.*\.(?:php|tpl|inc)$/', RecursiveRegexIterator::MATCH);

    $issues = [];

    foreach ($regexp_iterator as $file) {
        $filename = $file->getPathName();
        $log_if($verbose, "Checking #{magenta:%s}", $filename);
        if ($errors = $check($filename)) {
            $issues[$filename] = $errors;
        }
    }

    if (count($issues) > 0) {
        $issue_count = array_sum(array_map('count', $issues));
        $message = count($issues) === 1
                 ? '#{red:%u issue found in} #{red,bold:%s}'
                 : '#{red:%u issues found in} #{red,bold:%s}';
        $log_if(!$only_filenames, $message, $issue_count, $reduce($folder));

        foreach ($issues as $filename => $errors) {
            if ($only_filenames) {
                $log($filename);
            } else {
                $log('> File #{green,bold:%s}', $reduce($filename));
                foreach ($errors as $needle => $suggestion) {
                    $log('- #{cyan:%s} -> %s', $needle, $suggestion ?: '#{red:No suggestion available}');
                }
            }
        }
    }
}
