#!/usr/bin/env php
<?php
require_once 'studip_cli_env.inc.php';

if ($_SERVER['argc'] < 3) {
    fwrite(STDOUT, 'Stud.IP plugin localization tool - Tools for the localization of a plugin' . PHP_EOL);
    fwrite(STDOUT, '=========================================================================' . PHP_EOL);
    fwrite(STDOUT, 'Usage: ' . basename(__FILE__) . ' <folder> <command>' . PHP_EOL);
    fwrite(STDOUT, PHP_EOL);
    fwrite(STDOUT, '<folder> is the folder of the plugin you want to localize.' . PHP_EOL);
    fwrite(STDOUT, '<command> is any of the commands listed below.' . PHP_EOL);
    fwrite(STDOUT, PHP_EOL);
    fwrite(STDOUT, 'Commands:' . PHP_EOL);
    fwrite(STDOUT, '  detect  - Detects probably unmarked strings for localization in php files.' . PHP_EOL);
    fwrite(STDOUT, '  extract - Extracts the localizable string from php files into a .pot file.' . PHP_EOL);
    fwrite(STDOUT, '  compile - Compiles all .po files in the locale folder of the plugin' . PHP_EOL);
    fwrite(STDOUT, PHP_EOL);
    exit(0);
}

$plugin_folder = $_SERVER['argv'][1];
$command       = $_SERVER['argv'][2];

if (!is_dir($plugin_folder)) {
    $plugin_folder = rtrim($GLOBALS['ABSOLUTE_PATH_STUDIP'], '/') . '/' . ltrim($plugin_folder, '/');
}
if (!is_dir($plugin_folder)) {
    fwrite(STDERR, 'Error: ' . $_SERVER['argv'][2] . ' is not a valid folder' . PHP_EOL);
    exit(0);
}

$plugin_folder = rtrim($plugin_folder, '/');

if (!file_exists($plugin_folder. '/plugin.manifest')) {
    fwrite(STDERR, 'Error: ' . $_SERVER['argv'][2] . ' is not a valid plugin folder. Manifest is missing.' . PHP_EOL);
    exit(0);
}
$manifest = parse_ini_file($plugin_folder . '/plugin.manifest', false,  INI_SCANNER_RAW);

$languages = array_map(function ($lang) {
    return explode('_', $lang)[0];
}, array_keys($GLOBALS['INSTALLED_LANGUAGES']));

if ($command === 'detect') {
    $iterator = new RecursiveDirectoryIterator($plugin_folder, FilesystemIterator::FOLLOW_SYMLINKS | FilesystemIterator::UNIX_PATHS);
    $iterator = new RecursiveIteratorIterator($iterator);
    $regexp_iterator = new RegexIterator($iterator, '/\.php$/', RecursiveRegexIterator::MATCH);

    foreach ($regexp_iterator as $file) {
        $filename = $file->getPathName();
        if (preg_match('/(?<![$>])_\(/', file_get_contents($filename))) {
            fwrite(STDOUT, "{$filename}" . PHP_EOL);
        }
    }

    // system("ack -l '(?<![$>])_\(' {$plugin_folder}");
} elseif ($command === 'extract') {
    if (!isset($manifest['localedomain'])) {
        fwrite(STD_ERROR, 'No localedomain found in plugin manifest' . PHP_EOL);
    }

    $pot_name = $manifest['localedomain'];

    foreach (array_keys($GLOBALS['CONTENT_LANGUAGES']) as $lang) {
        $lang = explode('_', $lang)[0];
        $language_dir = "{$plugin_folder}/locale/{$lang}/LC_MESSAGES";
        if (!file_exists($language_dir)) {
            mkdir($language_dir, 0755, true);
        }
    }

    $main_lang = reset($languages);
    $pot_file  = "{$plugin_folder}/locale/{$main_lang}/LC_MESSAGES/{$pot_name}.pot";
    file_put_contents($pot_file, '');

    system("find {$plugin_folder} -iname '*.php' | xargs xgettext --keyword=_n:1,2 --from-code=UTF-8 -j -n --language=PHP --add-location=never --package-name={$manifest['pluginclassname']} -o {$pot_file}");
} elseif ($command === 'compile') {
    foreach (glob("{$plugin_folder}/locale/*/LC_MESSAGES/*.po") as $po) {
        $mo = preg_replace('/\.po$/', '.mo', $po);
        system("msgfmt {$po} -o {$mo}");
    }

} else {
    fwrite(STDERR, 'Unknown command: ' . $_SERVER['argv'][1] . PHP_EOL);
    exit(0);
}

exit(1);

