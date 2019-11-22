<?php
/**
 * Migrator.php - versioning databases using migrations
 *
 * Migrations can manage the evolution of a schema used by several physical
 * databases. It's a solution to the common problem of adding a field to make a
 * new feature work in your local database, but being unsure of how to push that
 * change to other developers and to the production server. With migrations, you
 * can describe the transformations in self-contained classes that can be
 * checked into version control systems and executed against another database
 * that might be one, two, or five versions behind.
 *
 * General concept
 *
 * Migrations can be described as a triple {sequence of migrations,
 * current schema version, target schema version}. The migrations are "deltas"
 * which are employed to change the schema of your physical database. They even
 * know how to reverse that change. These behaviours are mapped to the methods
 * #up and #down of class Migration. A migration is a subclass of that class and
 * you define the behaviours by overriding methods #up and #down.
 * Broadly spoken the current schema version as well as the target schema
 * version are "pointers" into the sequence of migrations. When migrating the
 * sequence of migrations is traversed between current and target version.
 * If the target version is greater than the current version, the #up methods
 * of the migrations up to the target version's migration are called. If the
 * target version is lower, the #down methods are used.
 *
 * Irreversible transformations
 *
 * Some transformations are destructive in a manner that cannot be reversed.
 * Migrations of that kind should raise an Exception in their #down method.
 *
 * Example of use:
 *
 * Create a directory which will contain your migrations. In this directory
 * create simple php files each containing a single subclass of class Migration.
 * Name this file with the following convention in mind:
 *
 * (\d+)_([a-z_]+).php   // (index)_(name).php
 *
 * 20180524110400_my_first_migration.php
 * 20180812152300_another_migration.php
 * 20181110100900_and_one_last.php
 *
 * Those numbers are used to order your migrations. Use the current time to
 * define the chronological order of migrations. Gaps are allowed. In previous
 * versions of the migration system, the numbers were naturally ordered starting
 * with 1 but that proved to be rather unflexible regarding bug fixes that
 * needed a migration to be executed. Thus, every executed migration number is
 * stored and you may add a migration lateron between two other migrations.
 *
 * When migrating those numbers are used to determine the migrations needed to
 * fulfill the target version.
 *
 * The current schema version must somehow be persisted using a subclass of
 * SchemaVersion.
 *
 * The name of the migration file is used to deduce the name of the subclass of
 * class Migration contained in the file. Underscores divide the name into words
 * and those words are then concatenated and camelcased.
 *
 * Examples:
 *
 * Name                |   Class
 * ----------------------------------------------------------------------------
 * my_first_migration  |  MyFirstMigration
 * another_migration   |  AnotherMigration
 * and_one_last        |  AndOneLast
 *
 * Those classes have to be subclasses of class Migration.
 *
 * Example:
 *
 * class MyFirstMigration extends Migration {
 *
 *   function description() {
 *     # put your code here
 *     # return migration description
 *   }
 *
 *   function up() {
 *     # put your code here
 *     # create a table for example
 *   }
 *
 *   function down() {
 *     # put your code here
 *     # delete that table
 *   }
 * }
 *
 * After writing your migrations you can invoke the migrator as follows:
 *
 *   $path = '/path/to/my/migrations';
 *
 *   $verbose = TRUE;
 *
 *   # instantiate a schema version persistor
 *   # this one is file based and will use a file in /tmp
 *   $version = new FileSchemaVersion('/tmp');
 *
 *   $migrator = new Migrator($path, $version, $verbose);
 *
 *   # now migrate to target version
 *   $migrator->migrateTo(20181128100139);
 *
 * If you want to migrate to the highest migration, you can just use NULL as
 * parameter:
 *
 *   $migrator->migrateTo(null);
 *
 * @author    Marcus Lunzenauer <mlunzena@uos.de>
 * @copyright 2007 Marcus Lunzenauer <mlunzena@uos.de>
 * @license   GPL2 or any later version
 * @package   migrations
 */
class Migrator
{
    const FILE_REGEXP = '/\b(\d+)([_\-][_a-z0-9]+)+\.php$/';

    /**
     * Direction of migration, either "up" or "down"
     *
     * @var string
     */
    private $direction;

    /**
     * Path to the migration files.
     *
     * @var string
     */
    private $migrations_path;

    /**
     * Specifies the target version, may be NULL (alias for "highest migration")
     *
     * @var int
     */
    private $target_version;

    /**
     * How verbose shall the migrator be?
     *
     * @var boolean
     */
    private $verbose;

    /**
     * The current schema version persistor.
     *
     * @var SchemaVersion
     */
    private $schema_version;


    /**
     * Constructor.
     *
     * @param string         a file path to the directory containing the migration
     *                       files
     * @param SchemaVersion  the current schema version persistor
     * @param boolean        verbose or not
     *
     * @return void
     */
    public function __construct($migrations_path, SchemaVersion $version, $verbose = false)
    {
        $this->migrations_path = $migrations_path;
        $this->schema_version  = $version;
        $this->verbose         = $verbose;
    }

    /**
     * Sanity check to prevent doublettes.
     *
     * @param array  an array of migration classes
     * @param int    the index of a migration
     */
    private function assertUniqueMigrationVersion($migrations, $version)
    {
        if (isset($migrations[$version])) {
            trigger_error(
                "Multiple migrations have the version number {$version}",
                E_USER_ERROR
            );
        }
    }

    /**
     * Invoking this method will perform the migrations with an index between
     * the current schema version (provided by the SchemaVersion object) and a
     * target version calling the methods #up and #down in sequence.
     *
     * @param mixed  the target version as an integer or NULL thus migrating to
     *               the top migration
     */
    public function migrateTo($target_version)
    {
        $migrations = $this->relevantMigrations($target_version);

        # you're on the right version
        if (empty($migrations)) {
            $this->log("You are already at %d.\n", $this->schema_version->get());
            return;
        }

        $this->log(
            "Currently at version %d. Now migrating %s to %d.\n",
            $this->schema_version->get(),
            $this->direction,
            $this->target_version
        );

        foreach ($migrations as $version => $migration) {
            $this->execute($version, $this->direction, $migration);
        }

        // Reset SORM cache
        SimpleORMap::expireTableScheme();
    }

    /**
     * Executes a migration's up or down method
     *
     * @param  string $version      Version to execute
     * @param  string $direction    Up or down
     * @param  Migration $migration Migration to execute (optional, will be
     *                              loaded if missing)
     */
    public function execute($version, $direction, Migration $migration = null)
    {
        if ($this->isUp($direction) && $this->schema_version->contains($version)) {
            $this->log("Version {$version} is already present.\n");
            return;
        }

        if ($this->isDown($direction) && !$this->schema_version->contains($version)) {
            $this->log("Version {$version} is not present.\n");
            return;
        }

        if ($migration === null) {
            $migrations = $this->migrationClasses();
            if (!isset($migrations[$version])) {
                throw new Exception("Version {$version} is invalid");
            }
            list($file, $class) = $migrations[$version];
            $migration = $this->loadMigration($file, $class);
        }

        $action = $this->isUp($direction) ? 'Migrating' : 'Reverting';

        $this->announce("{$action} %d", $version);
        if ($migration->description()) {
            $this->log($migration->description());
            $this->log(self::mark('', '-'));
        }

        $time_start = microtime(true);
        $migration->migrate($direction);

        $action = $this->isUp($direction) ? 'Migrated' : 'Reverted';
        $this->log('');
        $this->announce("{$action} in %ss", round(microtime(true) - $time_start, 3));
        $this->log('');

        if ($this->isDown($direction)) {
            $this->schema_version->remove($version);
        } else {
            $this->schema_version->add($version);
        }

    }

    /**
     * Invoking this method will return a list of migrations with an index between
     * the current schema version (provided by the SchemaVersion object) and a
     * target version calling the methods #up and #down in sequence.
     *
     * @param mixed  the target version as an integer or NULL thus migrating to
     *               the top migration
     *
     * @return array an associative array, whose keys are the migration's
     *               version and whose values are the migration objects
     */
    public function relevantMigrations($target_version)
    {
        // Load migrations
        $migrations = $this->migrationClasses();

        // Determine correct target version
        $this->target_version = $target_version === null
                              ? $this->topVersion()
                              : (int) $target_version;

        // Determine max version (might differ from max schema version in db in
        // development systems)
        $max_version = min($this->topVersion(), $this->schema_version->get());

        // Determine migration direction
        if ($this->target_version > 0 && $this->target_version >= $max_version) {
            $this->direction = 'up';
        } else {
            $this->direction = 'down';
        }

        // Sort migrations in correct order
        if ($this->isUp()) {
            ksort($migrations);
        } else {
            krsort($migrations);
        }

        $result = [];
        foreach ($migrations as $version => $migration_file_and_class) {
            if (!$this->relevantMigration($version)) {
                continue;
            }

            list($file, $class) = $migration_file_and_class;

            $result[$version] = $this->loadMigration($file, $class);
        }

        return $result;
    }

    /**
     * Checks wheter a migration has to be invoked, that is if the migration's
     * version is included in the interval between current and target schema
     * version.
     *
     * @param int   the migration's version to check for inclusion
     * @return bool TRUE if included, FALSE otherwise
     */
    private function relevantMigration($version)
    {
        if ($this->isUp()) {
            return !$this->schema_version->contains($version)
                && $version <= $this->target_version;
        } elseif ($this->isDown()) {
            return $this->schema_version->contains($version)
                && $version > $this->target_version;
        }

        return false;
    }

    /**
     * Loads a migration from the given file and creates and instance of it.
     *
     * @param string $file  File name of migration to load
     * @param string $class Class name to expect to be loaded from the file
     * @return Migration instance
     */
    private function loadMigration($file, $class)
    {
        if (class_exists($class)) {
            $migration = new $class($this->verbose);
        } else {
            $migration = require $file;
            if (!$migration instanceof Migration) {
                $migration = new $class($this->verbose);
            } else {
                $migration->setVerbose($this->verbose);
            }
        }
        return $migration;
    }

    /**
     * Am I migrating up?
     *
     * @return bool  TRUE if migrating up, FALSE otherwise
     */
    private function isUp($direction = null)
    {
        return ($direction ?: $this->direction) === 'up';
    }

    /**
     * Am I migrating down?
     *
     * @return bool  TRUE if migrating down, FALSE otherwise
     */
    private function isDown($direction = null)
    {
        return ($direction ?: $this->direction) === 'down';
    }

    /**
     * Maps a file name to a class name.
     *
     * @param string   part of the file name
     * @return string  the derived class name
     */
    protected function migrationClass($migration)
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $migration)));
    }

    /**
     * Returns the collection (an array) of all migrations in this migrator's
     * path.
     *
     * @return array  an associative array, whose keys are the migration's
     *                version and whose values are arrays containing the
     *                migration's file and class name.
     */
    public function migrationClasses()
    {
        $migrations = [];
        foreach ($this->migrationFiles() as $file) {
            list($version, $name) = $this->migrationVersionAndName($file);
            $this->assertUniqueMigrationVersion($migrations, $version);
            $migrations[$version] = [$file, $this->migrationClass($name)];
        }

        return $migrations;
    }

    /**
     * Return all migration file names from my migrations_path.
     *
     * @return array  a collection of file names
     */
    protected function migrationFiles()
    {
        $files = glob($this->migrations_path . '/*.php');
        $files = array_filter($files, function ($file) {
            return preg_match(self::FILE_REGEXP, $file);
        });
        return $files;
    }

    /**
     * Split a migration file name into that migration's version and name.
     *
     * @param string  a file name
     * @return array  an array of two elements containing the migration's version
     *                and name.
     */
    protected function migrationVersionAndName($migration_file)
    {
        $matches = [];
        preg_match(self::FILE_REGEXP, $migration_file, $matches);
        return [(int) $matches[1], $matches[2]];
    }

    /**
     * Returns the top migration's version.
     *
     * @return int  the top migration's version.
     */
    public function topVersion()
    {
        $versions = array_keys($this->migrationClasses());
        return $versions ? max($versions) : 0;
    }

    /**
     * Overridable method used to return a textual representation of what's going
     * on in me. You can use me as you would use printf.
     *
     * @param string $format just a dummy value, instead use this method as you
     *                       would use printf & co.
     */
    protected function log($format)
    {
        if (!$this->verbose) {
            return;
        }

        $args = func_get_args();
        vprintf(trim(array_shift($args)) . "\n", $args);
    }


    /**
     * Overridable method used to return a textual representation of a stronger
     * ouput of what's going on in me. You can use me as you would use printf.
     *
     * @param string $format just a dummy value, instead use this method as you
     *                       would use printf & co.
     */
    protected function announce($format)
    {
        # format message
        $args = func_get_args();
        $message = vsprintf(array_shift($args), $args);

        return $this->log(self::mark($message));
    }

    /**
     * Pads and highlights a given text to a specific length with the given
     * sign.
     *
     * @param string $text
     * @param string $sign
     */
    public static function mark($text, $sign = '=')
    {
        $text = trim($text);
        if ($text) {
            $text = " {$text} ";
        }
        return str_pad("{$sign}{$sign}{$text}", 79, $sign, STR_PAD_RIGHT);
    }
}
