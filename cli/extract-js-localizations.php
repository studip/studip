#!/usr/bin/env php
<?php
/**
 * extract-js-localizations.php
 *
 * Exports all strings from js into app/views/localizations/show.php so
 * they can be translated as well.
 *
 * @author    Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license   GPL2 or any later version
 * @copyright Stud.IP Core Group
 * @since     3.1
 */

require 'studip_cli_env.inc.php';

/**
 * Determines whether the file should be skipped depending on an exclude list
 * with an additional include list. This allows inclusion inside of previously
 * excluded entries. We need this for the assets directory.
 * Furthermore, the file is checked against a list of mime types to include.
 *
 * @param String $filename Adjusted filename (stripped to path inside trunk)
 * @param String $realfile Actual file name (needed for mime type detection)
 * @return bool indicating whether the file should be skipped or not
 */
function should_skip_file($filename, $realfile) {
    $exclude = [
        'locale/*',
        'node_modules/*',
        'public/assets/*',
        'public/pictures/*',
        'public/plugins_packages/*',
        'test/*',
        'vendor/*',
    ];
    $include = [
        'public/plugins_packages/core*',
    ];
    $mime_types = [
        'text/*',
        'application/javascript',
    ];

    // Check if the file should be excluded, depending on it's path.
    $matching_pattern = null;
    $skip             = false;
    foreach ($exclude as $pattern) {
        if (fnmatch($pattern, $filename)) {
            $matching_pattern = $pattern;
            $skip             = true;
            break;
        }
    }

    // If it should be skipped in step 1, check if it matches the include
    // patterns and no longer skip it, if it matches.
    // Matches are only from patterns that are longer than the pattern that
    // set the entry to be skipped. Thus it is detected if the file is in a
    // subdirectory.
    if ($skip) {
        foreach ($include as $pattern) {
            if (fnmatch($pattern, $filename) && mb_strlen($pattern) > mb_strlen($matching_pattern)) {
                $skip = false;
                break;
            }
        }
    }

    // If the file should not be skipped, check it's mime type and skip it
    // if the mime type is not allowed.
    if (!$skip && is_file($realfile)) {
        $mime_type = mime_content_type($realfile);

        $skip = true;
        foreach ($mime_types as $pattern) {
            if (fnmatch($pattern, $mime_type)) {
                $skip = false;
                break;
            }
        }
    }

    return $skip;
}

/**
 * Extract the actual text strings from a file. This will only detect single
 * line text strings. Multi line strings are just a hassle to handle in js
 * anyways.
 *
 * @param String $file Filename to extract text strings from
 * @return mixed Array with found text strings or false if no text strings
 *               were found
 */
function extract_strings($file) {
    $contents = file_get_contents($file);
    $regexp   = '/(?:\'([^\']+)\'|"([^"]+)")\\.toLocaleString\\(\\s*\\)/';

    if (preg_match_all($regexp, $contents, $matches, PREG_SET_ORDER)) {
        $result = [];
        foreach ($matches as $match) {
            $result[] = $match[1] ?: $match[2];
        }
        return array_unique($result);
    }

    return false;
}

/**
 * Recursively find text strings in files in the given directory.
 * This skips invalid files.
 *
 * @param String $directory Directory to search files in
 * @param mixed  $base      Optional base directory to strip from file names,
 *                          will default to the initial passed directory.
 * @return Array Associative array with filenames as index and an array of
 *               the text strings the file contains.
 */
function find_strings_in_dir($directory, $base = null) {
    $result = [];

    $base = rtrim($base ?: $directory, '/') . '/';

    $files = glob(rtrim($directory, '/') . '/*');
    foreach ($files as $file) {
        $filename = str_replace($base, '', $file);
        $is_dir   = is_dir($file);

        if (should_skip_file($filename, $file)) {
            continue;
        }

        if (is_dir($file)) {
            $result += find_strings_in_dir($file, $base);
        } elseif ($strings = extract_strings($file)) {
            $result[$filename] = $strings;
        }
    }

    return $result;
}

// Find text strings in all stud.ip files
$occurences = find_strings_in_dir(realpath(__DIR__ . '/..'));

// Remove duplicates
$hashes = [];
foreach ($occurences as $file => $strings) {
    foreach ($strings as $index => $string) {
        $hash = md5($string);
        if (in_array($hash, $hashes)) {
            unset($strings[$index]);
        } else {
            $hashes[] = $hash;
        }
    }
    if (empty($strings)) {
        unset($occurences[$file]);
    } else {
        $occurences[$file] = $strings;
    }
}

// Create trails view as output
ob_start();
?>
<?= '<?php' . PHP_EOL ?>

$translations = array(
<? foreach ($occurences as $file => $strings): ?>
    // <?= $file . PHP_EOL ?>
<? foreach ($strings as $string): ?>
    '<?= addcslashes($string, "'") ?>' => _('<?= addcslashes($string, "'") ?>'),
<? endforeach; ?>

<? endforeach; ?>
);

?>
<?= '<?=' ?> json_encode($translations) <?= '?>' ?>
<?
$view = ob_get_clean();

// Write output to the corresponding file
file_put_contents(__DIR__ . '/../app/views/localizations/show.php', $view);

// Show some statistics
printf('%u strings written to file' . PHP_EOL, array_sum(array_map('count', $occurences)));
