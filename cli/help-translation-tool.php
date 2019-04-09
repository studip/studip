#!/usr/bin/env php
<?php
/**
 * help-translation-tool.php
 *
 * Exports db data for the help content, tooltips and tours into a .po file or
 * reimports the translated strings into the db.
 *
 * Since we need to obtain the row to inssert/update the translated content,
 * this information is coded into the corresponding filename and line number.
 *
 * By using a specific range for line number, we can determine what type the
 * translated string is:
 *
 * range         | context    | location               | file  | line number
 * --------------+------------+------------------------+-------+-------------
 * 10000 - 19999 | -          | help_content.label     | route | position
 * 20000 - 29999 | content_id | help_content.content   | route | position
 * 30000 - 39999 | tour_id    | help_tours.name        | -     | version
 * 40000 - 49999 | tour_id    | help_tours.description | -     | version
 * 50000 - 59999 | tour_id    | help_tour_steps.title  | route | step
 * 60000 - 69999 | tour_id    | help_tour_steps.tip    | route | step
 * 70000 - 79999 | tooltip_id | help_tooltips.content  | route | version
 *
 * @author    Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license   GPL2 or any later version
 * @copyright Stud.IP Core Group
 * @since     3.1
 */

require_once 'studip_cli_env.inc.php';

define('MAX_LINE_LENGTH', 60);

/**
 * Escapes a string for use in .po file.
 *
 * @param String $string String to escape
 * @return String Escaped string
 */
function po_escape($string) {
    return str_replace('"', '\\"', $string);
}

/**
 * Unescapes a string for use in .po file.
 *
 * @param String $string String to unescape
 * @return String Unescaped string
 */
function po_unescape($string) {
    $replaces = [
        '\\"' => '"',
        '\\n' => "\n",
    ];
    $string = str_replace(array_keys($replaces), array_values($replaces), $string);
    return $string;
}

/**
 * Prepares a string for use in .po file.
 *
 * @param String $string String to use in .po file
 * @return String Processed string
 */
function po_stringify($string) {
    $string = str_replace("\r", '', $string);
    $chunks = explode("\n", $string);

    if (count($chunks) === 1 && mb_strlen($chunks[0]) < MAX_LINE_LENGTH) {
        return '"' . po_escape($chunks[0]) . '"';
    }

    $result = '""' . "\n";
    foreach ($chunks as $index => $chunk) {
        $chunk = wordwrap($chunk, MAX_LINE_LENGTH);
        $parts = explode("\n", $chunk);
        foreach ($parts as $idx => $line) {
            $current_last = $idx === count($parts) - 1;
            $last = ($current_last && $index === count($chunks) - 1);

            $result .= '"' . po_escape($line) . ($last ? '' : ($current_last ? '\\n' : ' ')) . '"'. "\n";
        }
    }
    return rtrim($result, "\n");
}

/**
 * Returns the id for a help entitiy based on the given index and other
 * credentials. This function also copies existing data and settings if
 * the entity in the given language is newly created.
 *
 * @param String $version  Stud.IP version to use for the new entry
 * @param String $language Language to use for the new entry
 * @param Array  $message  Complete message item from parsed .po file
 * @param String $route    Associated route (if any)
 * @param int    $index    Type index for the entity
 * @param int    $position Position/version of the entity
 * @return String Id of the entity
 */
function get_id($version, $language, $message, $route, $index, $position) {
    static $ids = [];

    if ($index < 3) {
        // Entity is help content
        $hash = md5('content#' . join('#', compact(words('temp version language route position'))));
    } elseif ($index < 7) {
        // Entity is help tour content
        $hash = md5('tour#' . $message['context'] . '#' . join('#' , compact(words('version language'))));
    } elseif ($index == 7) {
        // Entity is help tooltip
        $hash = md5('tooltip#' . $message['context'] . '#' . join(words('position language')));
    } else {
        throw new RuntimeException('Unknown index "' . $index . '"');
    }

    // If id has not yet been generated
    if (!isset($ids[$hash])) {
        if ($index < 3) {
            // Help content

            // Try to get content id by primary key
            $query = "SELECT content_id
                      FROM help_content
                      WHERE route = :route AND studip_version = :version
                        AND language = :language AND position = :position
                        AND custom = 0";
            $statement = DBManager::get()->prepare($query);
            $statement->bindValue(':route', $route);
            $statement->bindValue(':version', $version);
            $statement->bindValue(':language', $language);
            $statement->bindValue(':position', $position);
            $statement->execute();

            // Use found id or generate new one
            $id = $statement->fetchColumn() ?: md5(uniqid('help_content', true));
            $ids[$hash] = $id;
        } elseif ($index < 7) {
            // Help tour

            // Is there any previous generated content?
            // We have to use the hash generated above as the new id since
            // there is no other way to exactly identify an already created
            // entity for the given language and version
            $query = "SELECT tour_id
                      FROM help_tours
                      WHERE tour_id = :tour_id";
            $statement = DBManager::get()->prepare($query);
            $statement->bindValue(':tour_id', $hash);
            $statement->execute();

            $id = $statement->fetchColumn();
            if (!$id) {
                // If no previous generated content is available, prepare
                // database for new content
                $id = $hash;

                // Copy settings from tour
                $query = "INSERT INTO help_tours
                          SELECT :id AS tour_id, '' AS name, '' AS description,
                                 type, roles, version, :language AS language,
                                 :version AS studip_version, installation_id,
                                 UNIX_TIMESTAMP() AS mkdate
                          FROM help_tours
                          WHERE tour_id = :tour_id";
                $statement = DBManager::get()->prepare($query);
                $statement->bindValue(':id', $id);
                $statement->bindValue(':language', $language);
                $statement->bindValue(':version', $version);
                $statement->bindValue(':tour_id', $message['context']);
                $statement->execute();

                // Copy individual steps
                $query = "INSERT INTO help_tour_steps
                          SELECT :id AS tour_id, step, '' AS title, '' AS tip,
                                 orientation, interactive, css_selector, route,
                                 author_id, UNIX_TIMESTAMP() AS mkdate
                          FROM help_tour_steps
                          WHERE tour_id = :tour_id";
                $statement = DBManager::get()->prepare($query);
                $statement->bindValue(':id', $id);
                $statement->bindValue(':tour_id', $message['context']);
                $statement->execute();

                // Copy tour audiences
                $query = "INSERT INTO help_tour_audiences
                          SELECT :id AS tour_id, range_id, type
                          FROM help_tour_audiences
                          WHERE tour_id = :tour_id";
                $statement = DBManager::get()->prepare($query);
                $statement->bindValue(':id', $id);
                $statement->bindValue(':tour_id', $message['context']);
                $statement->execute();

                // Copy tour settings
                $query = "INSERT INTO help_tour_settings
                          SELECT :id AS tour_id, active, access
                          FROM help_tour_settings
                          WHERE tour_id = :tour_id";
                $statement = DBManager::get()->prepare($query);
                $statement->bindValue(':id', $id);
                $statement->bindValue(':tour_id', $message['context']);
                $statement->execute();
            }
            $ids[$hash] = $id;
        } elseif ($index == 7) {
            // Help tooltip

            // Nothing needs to be done, just copy the tooltip id
            // (This is the only table that has the id and version/language
            // info as primary key)
            $ids[$hash] = $message['context'];
        }
    }

    // Return id from cache
    return $ids[$hash];
}

// Error message: Not via cli or invalid parameters
if (!isset($_SERVER['argv'], $_SERVER['argc']) || $_SERVER['argc'] < 2) {
    print 'Usage: ' . (@$_SERVER['argv'][0] ?: basename(__FILE__)) . ' [--version] [--language] [--force] <import|export> [file]' . "\n";
    die(1);
}

// Parse command line options
$opts = [
    'short' => 'v:l:f',
    'long'  => [
        'force',
        'version:',
        'language:'
    ]
];
$options  = getopt($opts['short'], $opts['long']);
$force    = isset($options['f']) || isset($options['force']);
$version  = @$options['version'] ?: @$options['v']
          ?: DBManager::get()->query("SELECT MAX(studip_version) FROM help_content LIMIT 1")->fetchColumn()
          ?: $GLOBALS['SOFTWARE_VERSION'];
$language = @$options['language'] ?: @$options['l'] ?: mb_substr(Config::get()->DEFAULT_LANGUAGE, 0, 2);

// Remove option from arguments
$remove = [];
foreach (str_split($opts['short']) as $opt) {
    if ($opt !== ':') {
        $remove[] = '-' . $opt;
    }
}
foreach ($opts['long'] as $opt) {
    $remove[] = '--' . rtrim($opt, ':');
}
$_SERVER['argv'] = array_values(array_diff($_SERVER['argv'], $remove));

if ($_SERVER['argv'][1] === 'export') {
    // Export

    // Get output file name
    // Either from second parameter or use default at temp path
    $output   = $_SERVER['argv'][2] ?: ($GLOBALS['TMP_PATH'] . '/studip-help-content-' . $version . '-' . $language . '.po');

    // Error message: Script will not overwrite existing file unless forced
    if (file_exists($output) && !$force) {
        printf('Error: Output file "%s" exists. Use --force to overwrite.' . "\n", $output);
        die(2);
    }

    // Error message: Output directory does not exist
    $output_dir = dirname($output);
    if (!file_exists($output_dir)) {
        printf('Error: Directory for output "%s" does not exist.' . "\n", $output_dir);
        die(3);
    }
    // Error message: Output directory is not writable
    if (!is_writable($output_dir)) {
        printf('Error: Directory for output "%s" is not writable.' . "\n", $output_dir);
        die(4);
    }

    // Open output file for writing
    $fp = fopen($output, 'w+');
    // Error message: Output file could not be openend for writing
    if (!is_resource($fp)) {
        printf('Error: Could not open output file "%s" for writing.' . "\n", $output);
        die(5);
    }

    // Write .po header
    fputs($fp, '# Jan-Hendrik Willms <tleilax+studip@gmail.com>, 2014.' . "\n");
    fputs($fp, '# Generated content' . "\n");
    fputs($fp, 'msgid ""' . "\n");
    fputs($fp, 'msgstr ""' . "\n");
    fputs($fp, '"Project-Id-Version: STUDIP-' . $GLOBALS['SOFTWARE_VERSION'] . '\\n"' . "\n");
    fputs($fp, '"Language: STUDIP-' . $language . '\\n"' . "\n");
    fputs($fp, '"Report-Msgid-Bugs-To: tleilax+studip@gmail.com' . '\\n"' . "\n");
    fputs($fp, '"POT-Creation-Date: ' . date('r') . '\\n"' . "\n");
    fputs($fp, '"PO-Revision-Date: ' . date('r') . '\\n"' . "\n");
    fputs($fp, '"Last-Translator: Stud.IP Core Group <info@studip.de>\\n"' . "\n");
    fputs($fp, '"Language-Team: Stud.IP Core Group <info@studip.de>\\n"' . "\n");
    fputs($fp, '"MIME-Version: 1.0\\n"' . "\n");
    fputs($fp, '"Content-Type: text/plain; charset=UTF-8\\n"' . "\n");
    fputs($fp, '"Content-Transfer-Encoding: 8bit\\n"' . "\n");
    fputs($fp, "\n");

    // Load all data from db in one big query
    $query = "SELECT label AS content, CONCAT(route, ':', 10000 + position) AS occurence
              FROM help_content
              WHERE studip_version = :version
                AND language = :language
                AND custom = 0
              -- Help content label

              UNION

              SELECT CONCAT(content, '{#$#}', content_id) AS content, CONCAT(route, ':', 20000 + position) AS occurence
              FROM help_content
              WHERE studip_version = :version
                AND language = :language
                AND custom = 0
              -- Actual help content

              UNION

              SELECT CONCAT(name, '{#$#}', tour_id) AS content, CONCAT('tours.php:', 30000 + version) AS occurence
              FROM help_tours
              WHERE studip_version = :version
                AND language = :language
              -- Help tour name

              UNION

              SELECT CONCAT(description, '{#$#}', tour_id) AS content, CONCAT('tours.php:', 40000 + version) AS occurence
              FROM help_tours
              WHERE studip_version = :version
                AND language = :language
              -- Help tour description

              UNION

              SELECT CONCAT(title, '{#$#}', tour_id) AS content, CONCAT(route, ':', 50000 + step) AS occurence
              FROM help_tour_steps
              JOIN help_tours USING (tour_id)
              WHERE studip_version = :version
                AND language = :language
              -- Individual help tour step title

              UNION

              SELECT CONCAT(tip, '{#$#}', tour_id) AS content, CONCAT(route, ':', 60000 + step) AS occurence
              FROM help_tour_steps
              JOIN help_tours USING (tour_id)
              WHERE studip_version = :version
                AND language = :language
              -- Individual help tour step content

              UNION

              SELECT CONCAT(t0.content, '{#$#}', t0.tooltip_id) AS content, CONCAT(t0.route, ':', 70000 + t0.version) AS occurence
              FROM help_tooltips AS t0
              LEFT JOIN help_tooltips AS t1
                     ON     t0.language = t1.language
                        AND t0.tooltip_id = t1.tooltip_id
                        AND t0.version < t1.version
              WHERE t0.language = :language AND t1.tooltip_id IS NULL
              -- Help tooltip
              ";
    $statement = DBManager::get()->prepare($query);
    $statement->bindValue(':version', $version);
    $statement->bindValue(':language', $language);
    $statement->execute();
    $statement->setFetchMode(PDO::FETCH_GROUP | PDO::FETCH_COLUMN);

    // Loop through each row and write .po entry
    foreach ($statement as $content => $occurences) {
        list($content, $context) = explode('{#$#}', $content);

        fputs($fp, '#: ' . implode(' ', $occurences) . "\n");
        if ($context) {
            fputs($fp, 'msgctxt "' . $context . '"' . "\n");
        }
        fputs($fp, 'msgid ' . po_stringify($content) . "\n");
        fputs($fp, 'msgstr ""' . "\n");
        fputs($fp, "\n");
    }

    // Close output file
    fclose($fp);
} elseif ($_SERVER['argv'][1] === 'import') {
    // Import

    // Error message: Invalid parameters
    if ($_SERVER['argc'] < 4) {
        print 'Usage: ' . $_SERVER['argv'][0] . ' import [--language] <file> <version>';
        die(6);
    }

    // Set input file and version from parameters
    $input   = $_SERVER['argv'][2];
    $version = $_SERVER['argv'][3];

    // Error message: Input file does not exists or is not readable
    if (!file_exists($input) || !is_readable($input)) {
        printf('Error: Input file "%s" does not exist or is not readable.' . "\n", $input);
        die(7);
    }

    // Open input file for reading
    $fp = fopen($input, 'r');
    // Error message: Input file could not be opened for reading
    if (!is_resource($fp)) {
        printf('Error: Could not open input file "%s" for reading.' . "\n", $input);
        die(5);
    }

    // Parse input .po file
    // This is pretty straight forward, yet hacky.
    // The script tries to detect comments (only #:, # by itself is ignored),
    // message context, message id and message content in this order.
    // Any empty line will write to messages array.
    // This routine will probably break for any .po file that differs from the
    // ones created in transifex.
    // This is just supposed to work, not to be beautiful. ;)
    $messages = [];
    $context    = '';
    $id         = '';
    $content    = '';
    $occurences = [];
    $last       = false;
    $count      = 0;
    while (!feof($fp) && $row = fgets($fp)) {
        $count += 1;

        $row = trim($row);
        if ($row[0] === '#' && $row[1] !== ':') {
            continue;
        }
        if ($row[0] === '#') {
            $occurences = array_merge($occurences, explode(' ', mb_substr($row, 2)));
            $occurences = array_filter($occurences);
            $last = 'occurence';
        } elseif (preg_match('/^\msgctxt\\s+"(.*?)"$/', $row, $match)) {
            $context = $match[1];
            $last = 'context';
        } elseif (preg_match('/^msgid\\s+"(.*?)"$/', $row, $match)) {
            $id = po_unescape($match[1]);
            $last = 'id';
        } elseif (preg_match('/^msgstr\\s+"(.*?)"$/', $row, $match)) {
            $content = po_unescape($match[1]);
            $last = 'content';
        } elseif (preg_match('/^"(.*?)"$/', $row, $match) && in_array($last, words('id content'))) {
            if ($last === 'id') {
                $id .= po_unescape($match[1]);
            } else {
                $content .= po_unescape($match[1]);
            }
        } elseif (!$row && $last === 'content') {
            $messages[$context . '#' . $id] = compact(words('context id content occurences'));

            $context    = '';
            $id         = '';
            $content    = '';
            $occurences = [];
            $last       = false;
        } else {
            printf('Parse error at line %u.' . "\n", $count);
            printf('Last item was "%s".' . "\n", $last);
            printf('Current row: %s' . "\n", $row);
            die(6);
        }
    }
    fclose($fp);

    // Parse meta information (no context & no id = item at '#')
    $meta = [];
    foreach (explode("\n", $messages['#']['content']) as $row) {
        $row = trim($row);
        if (!$row) {
            continue;
        }

        list($index, $content) = array_map('trim', explode(':', $row, 2));
        $meta[$index] = $content;
    }
    unset($messages['#']);

    // Get language
    $language = mb_strtolower($meta['Language']);

    // Define db queries for each type (see comment block at the top of
    // this file, type is distinguished by the line number / 10000)
    $queries = [];
    $queries[1] = "INSERT INTO help_content (content_id, language, label, icon, content, route, studip_version, position, custom, installation_id, mkdate)
                   VALUES (:id, :language, :content, 'info', '', :route, :version, :position, 0, '', UNIX_TIMESTAMP())
                   ON DUPLICATE KEY UPDATE label = VALUES(label)";
    $queries[2] = "INSERT INTO help_content (content_id, language, label, icon, content, route, studip_version, position, custom, installation_id, mkdate)
                   VALUES (:id, :language, '', 'info', :content, :route, :version, :position, 0, '', UNIX_TIMESTAMP())
                   ON DUPLICATE KEY UPDATE content = VALUES(content)";
    $queries[3] = "INSERT INTO help_tours (tour_id, name, description, type, roles, version, language, studip_version, installation_id, mkdate)
                   VALUES (:id, :content, '', 'tour', '', :position, :language, :version, '', UNIX_TIMESTAMP())
                   ON DUPLICATE KEY UPDATE name = VALUES(name)";
    $queries[4] = "INSERT INTO help_tours (tour_id, name, description, type, roles, version, language, studip_version, installation_id, mkdate)
                   VALUES (:id, '', :content, 'tour', '', :position, :language, :version, '', UNIX_TIMESTAMP())
                   ON DUPLICATE KEY UPDATE description = VALUES(description)";
    $queries[5] = "INSERT INTO help_tour_steps (tour_id, step, title, tip, interactive, css_selector, route, author_id, mkdate)
                   VALUES (:id, :position, :content, '', 0, '', :route, '', UNIX_TIMESTAMP())
                   ON DUPLICATE KEY UPDATE title = VALUES(title)";
    $queries[6] = "INSERT INTO help_tour_steps (tour_id, step, title, tip, interactive, css_selector, route, author_id, mkdate)
                   VALUES (:id, :position, '', :content, 0, '', :route, '', UNIX_TIMESTAMP())
                   ON DUPLICATE KEY UPDATE tip = VALUES(tip)";
    $queries[7] = "INSERT INTO help_tooltips (tooltip_id, language, version, content, author_id, mkdate, route)
                   VALUES (:id, :language, :position, :content, '', UNIX_TIMESTAMP(), :route)
                   ON DUPLICATE KEY UPDATE content = VALUES(content)";

    // Prepare statements and prebind version and language
    $statements = array_map([DBManager::get(), 'prepare'], $queries);
    foreach ($statements as $index => $statement) {
        $statement->bindValue(':version', $version);
        $statement->bindValue(':language', $language);

        $statements[$index] = $statement;
    }

    // Process each message, skip the ones with empty content
    foreach ($messages as $message) {
        if (empty($message['content'])) {
            continue;
        }

        foreach ($message['occurences'] as $occurence) {
            list($route, $lineno) = explode(':', $occurence);
            $index    = floor($lineno / 10000);
            $position = $lineno % 10000;

            $id = get_id($version, $language, $message, $route, $index, $position);

            $statement = $statements[$index];
            $statement->bindValue(':id', $id);
            $statement->bindValue(':content', $message['content']);
            $statement->bindValue(':route', $route);
            $statement->bindValue(':position', $position);
            $statement->execute();
        }
    }
}
