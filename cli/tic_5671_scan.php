#!/usr/bin/env php
<?php
require_once 'studip_cli_env.inc.php';

$opts = getopt('fhnosv', ['filenames', 'help', 'non-recursive', 'occurences', 'matches', 'verbose']);

if (isset($opts['h']) || isset($opts['help'])) {
    fwrite(STDOUT, 'TIC 5671 Scanner - Scans files for occurences of globalized config items' . PHP_EOL);
    fwrite(STDOUT, '========================================================================' . PHP_EOL);
    fwrite(STDOUT, 'Usage: ' . basename(__FILE__) . ' [OPTION] [FOLDER] [FOLDER2] ..' . PHP_EOL);
    fwrite(STDOUT, PHP_EOL);
    fwrite(STDOUT, '[FOLDER] will default to Stud.IP base folder.' . PHP_EOL);
    fwrite(STDOUT, 'Supply many folders if you need to.' . PHP_EOL);
    fwrite(STDOUT, 'You may pass the special value of "plugins" to scan the plugin folder.' . PHP_EOL);
    fwrite(STDOUT, PHP_EOL);
    fwrite(STDOUT, 'Options:' . PHP_EOL);
    fwrite(STDOUT, ' -h, --help               Display this help' . PHP_EOL);
    fwrite(STDOUT, ' -f, --filenames          Display only filenames (excludes -m and -o)' . PHP_EOL);
    fwrite(STDOUT, ' -n, --non-recursive      Do not scan recursively into subfolders' . PHP_EOL);
    fwrite(STDOUT, ' -m, --matches            Show matched config variables' . PHP_EOL);
    fwrite(STDOUT, ' -o, --occurences         Display occurences in files (implies -s)' . PHP_EOL);
    fwrite(STDOUT, ' -v, --verbose            Print additional information' . PHP_EOL);
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

$verbose         = isset($opts['v']) || isset($opts['verbose']);
$only_filenames  = isset($opts['f']) || isset($opts['filenames']);
$show_occurences = $verbose || isset($opts['o']) || isset($opts['occurences']);
$show_matches    = $show_occurences || isset($opts['m']) || isset($opts['matches']);
$recursive       = !(isset($opts['n']) || isset($opts['recursive']));
$folders         = $args ?: [$GLOBALS['STUDIP_BASE_PATH']];

// Prepare logging mechanism
$log = function ($message) {
    $ansi = [
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
    ];
    
    $message = trim($message);

    if ($message) {
        $ansi_codes = implode('|', array_keys($ansi));
        if (preg_match_all('/#\{((?:(?:' . $ansi_codes . '),?)+):(.+?)\}/s', $message, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $chunk = '';
                $codes = explode(',', $match[1]);
                foreach ($codes as $code) {
                    $chunk .= "\033[{$ansi[$code]}m";
                }
                $chunk .= $match[2] . "\033[{$ansi[off]}m";
                
                $message = str_replace($match[0], $chunk, $message);
            }
        }
        
        $args = array_slice(func_get_args(), 1);
        vprintf($message . "\n", $args);
    }
};
$log_if = function ($condition, $message) use ($log) {
    if ($condition) {
        call_user_func_array($log, array_slice(func_get_args(), 1));
    }
};

// Prepare line highlighter
$highlight = function ($content, $variable) {
    $lines = explode("\n", $content);
    
    $result = [];
    foreach ($lines as $index => $line) {
        if (mb_strpos($line, $variable) === false) {
            continue;
        }
        $result[$index + 1] = $line;
    }

    if (!$result) {
        return '';
    }

    $max = max(array_map('mb_strlen', array_keys($result)));

    foreach ($result as $index => $line) {
        $result[$index] = sprintf('#{yellow:%0' . $max . 'u}: %s', $index, str_replace($variable, "#{yellow_bg,black:$variable}", $line));
    }
    
    return implode("\n", $result);
};

// Prepare folders
foreach ($folders as $index => $folder) {
    if ($folder === 'plugins') {
        $folders[$index] = $GLOBALS['STUDIP_BASE_PATH'] . '/public/plugins_packages/';
    }
}
$folders = array_unique($folders);

// Prepare regexp from regexp
$config = Config::get()->getFields('global');
$quoted = array_map(function ($item) { return preg_quote($item, '/'); }, $config);
$regexp = '/\$(?:GLOBALS\[["\']?)?(' . implode('|', $quoted) . ')\b/S';

// Engage
foreach ($folders as $folder) {
    if (!file_exists($folder) || !is_dir($folder)) {
        $log_if($verbose, 'Skipping non-folder arg #{red:%s}', $folder);
        continue;
    }
    $log_if($verbose, 'Scanning "%s"', $folder);
    if ($recursive) {
        $iterator = new RecursiveDirectoryIterator($folder, FilesystemIterator::FOLLOW_SYMLINKS | FilesystemIterator::UNIX_PATHS);
        $iterator = new RecursiveIteratorIterator($iterator);
    } else {
        $iterator = new DirectoryIterator($folder);
    }
    $regexp_iterator = new RegexIterator($iterator, '/.*\.(?:php|tpl|inc)$/', RecursiveRegexIterator::MATCH);

    foreach ($regexp_iterator as $file) {
        $filename = $file->getPathName();
        $contents = file_get_contents($filename);
        $log_if($verbose, "Checking #{magenta:%s}", $filename);
        if ($matched = preg_match_all($regexp, $contents, $matches)) {
            if ($only_filenames) {
                $log($filename);
            } else {
                $log('%u matched variable(s) in #{green,bold:%s}', $matched, $filename);
                if ($show_matches) {
                    $variables = array_unique($matches[1]);
                    foreach ($variables as $variable) {
                        $log('>> #{cyan:%s}', $variable);
                        $log_if($show_occurences, $highlight($contents, $variable));
                    }
                }
            }
        }
    }
}
