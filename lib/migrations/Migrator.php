<?php
# Lifter007: TEST

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
 * 001_my_first_migration.php
 * 002_another_migration.php
 * 003_and_one_last.php
 *
 * Those numbers are used to order your migrations. The first migration has
 * to be a 1 (but you can use leading 0). Every following migration has to be
 * the successor to the previous migration. No gaps are allowed. Just use
 * natural numbers starting with 1.
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
 *   $migrator->migrate_to(5);
 *
 * If you want to migrate to the highest migration, you can just use NULL as
 * parameter:
 *
 *   $migrator->migrate_to(NULL);
 *
 * @author    Marcus Lunzenauer <mlunzena@uos.de>
 * @copyright 2007 Marcus Lunzenauer <mlunzena@uos.de>
 * @license   GPL2 or any later version
 * @package   migrations
 */
class Migrator
{
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
    private function assert_unique_migration_version($migrations, $version)
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
    public function migrate_to($target_version)
    {
        $migrations = $this->relevant_migrations($target_version);

        # you're on the right version
        if (empty($migrations)) {
            $this->log("You are already at %d.\n", $this->schema_version->get());
            return;
        }

        $this->log(
            "Currently at version %d. Now migrating %s to %s.\n",
            $this->schema_version->get(),
            $this->direction,
            $this->target_version
        );

        foreach ($migrations as $version => $migration) {
            $class = get_class($migration);
            $this->log("\n\nNext migration: %s (%d)\n\n", $class, $version);
            $time_start = microtime(true);
            $migration->migrate($this->direction);
            $this->log("\n\nmigration: %s took %s s\n\n", $class, round(microtime(true) - $time_start, 3));
            $this->schema_version->set($this->is_down() ? $version - 1 : $version);
        }

        // Reset SORM cache
        SimpleORMap::expireTableScheme();
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
    public function relevant_migrations($target_version)
    {
        $this->target_version = $target_version === null
                              ? $this->top_version()
                              : (int) $target_version;

        # migrate up
        if ($this->schema_version->get() < $this->target_version) {
            $this->direction = 'up';
        }
        # migrate down
        elseif ($this->schema_version->get() > $this->target_version) {
            $this->direction = 'down';
        }


        $migrations = $this->migration_classes();
        if ($this->is_up()) {
            ksort($migrations);
        } else {
            krsort($migrations);
        }

        $result = [];

        foreach ($migrations as $version => $migration_file_and_class) {
            if (!$this->relevant_migration($version)) {
                continue;
            }

            list($file, $class) = $migration_file_and_class;

            require_once $file;
            $migration = new $class($this->verbose);

            $result[$version] = $migration;
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
    private function relevant_migration($version)
    {
        $current_version = $this->schema_version->get();

        if ($this->is_up()) {
            return $current_version < $version
                && $version <= $this->target_version;
        } elseif ($this->is_down()) {
            return $current_version >= $version
                && $version > $this->target_version;
        }

        return false;
    }

    /**
     * Am I migrating up?
     *
     * @return bool  TRUE if migrating up, FALSE otherwise
     */
    private function is_up()
    {
        return $this->direction === 'up';
    }

    /**
     * Am I migrating down?
     *
     * @return bool  TRUE if migrating down, FALSE otherwise
     */
    private function is_down()
    {
        return $this->direction === 'down';
    }

    /**
     * Maps a file name to a class name.
     *
     * @param string   part of the file name
     * @return string  the derived class name
     */
    protected function migration_class($migration)
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
    public function migration_classes()
    {
        $migrations = [];
        foreach ($this->migration_files() as $file) {
            list($version, $name) = $this->migration_version_and_name($file);
            $this->assert_unique_migration_version($migrations, $version);
            $migrations[$version] = [$file, $this->migration_class($name)];
        }

        return $migrations;
    }

    /**
     * Return all migration file names from my migrations_path.
     *
     * @return array  a collection of file names
     */
    protected function migration_files()
    {
        $files = glob($this->migrations_path . '/[0-9]*_*.php');
        return $files;
    }

    /**
     * Split a migration file name into that migration's version and name.
     *
     * @param string  a file name
     * @return array  an array of two elements containing the migration's version
     *                and name.
     */
    protected function migration_version_and_name($migration_file)
    {
        $matches = [];
        preg_match('/\b([0-9]+)_([_a-z0-9]*)\.php$/', $migration_file, $matches);
        return [(int) $matches[1], $matches[2]];
    }

    /**
     * Returns the top migration's version.
     *
     * @return int  the top migration's version.
     */
    public function top_version()
    {
        $versions = array_keys($this->migration_classes());
        return $versions ? max($versions) : 0;
    }

    /**
     * Overridable method used to return a textual representation of what's going
     * on in me. You can use me as you would use printf.
     *
     * @param string  just a dummy value, instead use this method as you would use
     *                printf & co.
     */
    protected function log($format)
    {
        if (!$this->verbose) {
            return;
        }

        $args = func_get_args();
        vprintf(array_shift($args), $args);
    }
}
