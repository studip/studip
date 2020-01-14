<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      André Noack <noack@data-quest.de>
 * @author      <mlunzena@uos.de>
 * @author      Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @copyright   2015 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
*/

/**
 * The StudipAutoloader takes care for automatically loading
 * classes. You just have to provide it with a couple of paths where
 * it may find the classes.
 *
 * Example:
 * \code
 * StudipAutoloader::register();
 * StudipAutoloader::addAutoloadPath("/tmp");
 * StudipAutoloader::addAutoloadPath("[...]lib/classes");
 *
 * // Add namespace prefix that indicates that class with the given
 * // namespace will be found in the given directory
 * StudipAutoloader::addAutoloadPath("[...]lib/classes", "Studip");
 * \endcode
 *
 * Additionally you may define single class lookups (or a bundle of them).
 *
 * Example:
 * \code
 * StudipAutoloader::addClassLookup('FooClass', '[...]/bar/foo.php');
 * StudipAutoloader::addClassLookups(array(
 *     'Bar' => '[...]/somwhere/inside_this.php',
 *     'Baz' => '[...]/elsewhere/b_a_z.class.php',
 * ));
 * \encode
 */
class StudipAutoloader
{
    public static $autoload_paths = [];
    public static $class_lookup = [];

    // List of all possible file extensions that are tested when trying to
    // find a class. This list should be ordered by the probability that
    // a file has this extension (often used extensions first) so that the
    // file is found quickly and unneccessary, costly calls to file_exists()
    // can be avoided.
    protected static $file_extensions = [
        '.class.php',
        '.php',
        '.interface.php',
    ];

    /**
     * Registers the StudipAutoloader as an autoloader.
     */
    public static function register()
    {
        spl_autoload_register('static::loadClass');
    }


    /**
     * Un-registers the StudipAutoloader again.
     */
    public static function unregister()
    {
        spl_autoload_unregister('static::loadClass');
    }


    /**
     * Adds another path to the list of paths where to search for
     * classes.
     * You may also pass an optional namespace prefix that indicates
     * that class that start with this prefix are found in the given
     * path.
     *
     * @param string $path   the path to add
     * @param string $prefix the optional namespace prefix
     */
    public static function addAutoloadPath($path, $prefix = '')
    {
        $path = realpath($path);
        if ($prefix) {
            $prefix = rtrim($prefix, '\\') . '\\';
        }

        self::$autoload_paths[] = compact('path', 'prefix');
    }


    /**
     * Removes a path from the list of paths.
     *
     * @param string $path   the path to remove
     * @param string $prefix the optional namespace prefix
     */
    public static function removeAutoloadPath($path, $prefix = '')
    {
        $path = realpath($path);

        foreach (self::$autoload_paths as $index => $item) {
            if ($item['path'] === $path && $item['prefix'] === $prefix) {
                unset(self::$autoload_paths[$index]);
            }
        }
    }

    /**
     * Add a class and it's location to the lookup table.
     * You may also pass an array of class that point the same file
     * in case of combined vendor packages.
     *
     * @param mixed $class Class name
     * @param String $path Path to file
     * @since Stud.IP 3.4
     */
    public static function addClassLookup($class, $path)
    {
        foreach ((array)$class as $one_class) {
            self::$class_lookup[$one_class] = $path;
        }
    }

    /**
     * Adds a bundle of classes and their locations to the lookup table.
     *
     * @param Array $map Associative array of class name and their locations
     * @since Stud.IP 3.4
     */
    public static function addClassLookups(array $map)
    {
        self::$class_lookup = array_merge(self::$class_lookup, $map);
    }

    /**
     * Remove class from lookup table.
     *
     * @param String $class Class name
     * @since Stud.IP 3.4
     */
    public static function removeClassLookup($class)
    {
        unset(self::$class_lookup[$class]);
    }

    /**
     * Loads the specified class or interface.
     *
     * @param  string    $class  the name of the class
     * @return bool|null true, if loaded, otherwise null
     */
    public static function loadClass($class)
    {
        $file = self::lookupClass($class) ?: self::findFile($class);

        if ($file) {
            self::$class_lookup[$class] = $file;

            include $file;

            return true;
        }
    }

    /**
     * Tries to locate a class in the lookup array.
     *
     * @param string $class Class name
     * @return string with filename or false
     */
    private static function lookupClass($class)
    {
        if (!isset(self::$class_lookup[$class])) {
            return false;
        }

        $file = self::$class_lookup[$class];
        if (file_exists($file)) {
            return $file;
        }
        unset(self::$class_lookup[$class]);
        return false;
    }

    /**
     * Locate the file where the class is defined.
     * Handles possible namespaces by mapping the path elements to the
     * directory structure.
     *
     * @param string $class  the name of the class
     * @return string|null   the path, if found, otherwise null
     */
    private static function findFile($class)
    {
        foreach (self::$autoload_paths as $item) {
            $class_file = self::convertClassToFilename($class, $item['prefix']);
            if ($class_file === false) {
                continue;
            }

            $filename = self::resolvePathAndFilename($item['path'], $class_file);
            if ($filename !== false) {
                return $filename;
            }
        }
    }

    /**
     * Convert the raw php class name to a potential file name. Namespaces are taken
     * into account.
     *
     * @param string $class  the name of the class
     * @param string $prefix the optional namespace prefix
     * @return string containing the resolved file name.
     */
    private static function convertClassToFilename($class, $prefix = '')
    {
        // Test whether the namespace prefix matches the class name, leave early if not
        if ($prefix && mb_strpos($class, $prefix) !== 0) {
            return false;
        }

        // Remove namespace prefix
        $class = mb_substr($class, mb_strlen($prefix));

        // Convert namespace into directory structure
        $namespaced = str_replace('\\', DIRECTORY_SEPARATOR, $class);
        $filename   = dirname($namespaced) . DIRECTORY_SEPARATOR . basename($namespaced);

        return $filename;
    }

    /**
     * Resolves a path and class filename to the actual filename on disk.
     * If the given path does not exist, we will try to resolve it
     * case-insenitive-ish (we will try to lowercase version but not all
     * combinations, so rather pseudo case-insensitive).
     *
     * @param String $path       Path to file
     * @param String $class_file Base name of the class
     * @return mixed Fully qualified filename or false if no match was found
     */
    private static function resolvePathAndFilename($path, $class_file)
    {
        // Skip invalid paths immediately
        if (!is_dir($path)) {
            return false;
        }

        // Create full filename without extension
        $filename = $path . DIRECTORY_SEPARATOR . $class_file;
        $fullpath = dirname($filename);

        // Path is invalid? Try to resolve it step by step.
        if (!is_dir($fullpath)) {
            $chunks = array_filter(explode(DIRECTORY_SEPARATOR, $fullpath));
            $path = '';
            foreach ($chunks as $chunk) {
                if (!is_dir($path . DIRECTORY_SEPARATOR . $chunk)) {
                    $chunk = mb_strtolower($chunk);
                }
                if (!is_dir($path . DIRECTORY_SEPARATOR . $chunk)) {
                    return false;
                }
                $path .= DIRECTORY_SEPARATOR . $chunk;
            }

            $class_file = basename($filename);
        }

        // Valid basename, try registered extensions
        $base = $path . DIRECTORY_SEPARATOR . $class_file;
        foreach (self::$file_extensions as $extension) {
            if (file_exists($base . $extension)) {
                return $base . $extension;
            }
        }
        return false;
    }
}
