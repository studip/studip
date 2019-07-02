<?php
/**
 * Migration.php - abstract base class for migrations
 *
 * This class serves as the abstract base class for all migrations.
 *
 * @author    Marcus Lunzenauer <mlunzena@uos.de>
 * @copyright 2007 - Marcus Lunzenauer <mlunzena@uos.de>
 * @license   GPL2 or any later version
 * @package   migrations
 */
abstract class Migration
{
    /**
     * use verbose output
     *
     * @var boolean
     */
    private $verbose;

    /**
     * Initalize a Migration object (optionally using verbose output).
     *
     * @param boolean $verbose verbose output (default FALSE)
     */
    public function __construct($verbose = false)
    {
        $this->setVerbose($verbose);
    }

    /**
     * Sets the verbose state of this migration.
     * @param boolean $state Verbosity state
     */
    public function setVerbose($state = true)
    {
        $this->verbose = (bool) $state;
    }

    /**
     * Abstract method describing this migration step.
     * This method should be implemented in a migration subclass.
     *
     * @return string migration description
     */
    public function description()
    {
    }

    /**
     * Abstract method performing this migration step.
     * This method should be implemented in a migration subclass.
     */
    protected function up()
    {
    }

    /**
     * Abstract method reverting this migration step.
     * This method should be implemented in a migration subclass.
     */
    protected function down()
    {
    }

    /**
     * Perform or revert this migration, depending on the indicated direction.
     *
     * @param string $direction migration direction (either 'up' or 'down')
     */
    public function migrate($direction)
    {
        if (!in_array($direction, ['up', 'down'])) {
            return;
        }

        $result = $this->$direction();

        return $result;
    }

    /**
     * Print the given string (if verbose output is enabled).
     *
     * @param string $text text to print
     */
    protected function write($text = '')
    {
        if ($this->verbose) {
            echo "{$text}\n";
        }
    }

    /**
     * Print the given formatted string (if verbose output is enabled).
     * Output always includes the migration's class name.
     *
     * @param string $format,... printf-style format string and parameters
     */
    protected function announce($format /* , ... */)
    {
        # format message
        $args = func_get_args();
        $message = vsprintf(array_shift($args), $args);

        return $this->write(Migrator::mark($message));
    }
}
