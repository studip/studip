<?php
# Lifter007: TEST

/**
 * db_schema_version.php - database backed schema versions
 *
 * Implementation of SchemaVersion interface using a database table.
 *
 * @author    Elmar Ludwig
 * @copyright 2007 Elmar Ludwig
 * @license    GPL2 or any later version
 * @package migrations
 */
class DBSchemaVersion extends SchemaVersion
{
    /**
     * domain name of schema version
     *
     * @var string
     */
    private $domain;

    /**
     * current schema version number
     *
     * @var int
     */
    private $version;

    /**
     * Initialize a new DBSchemaVersion for a given domain.
     * The default domain name is 'studip'.
     *
     * @param string $domain domain name (optional)
     */
    public function __construct($domain = 'studip')
    {
        $this->domain = $domain;
        $this->version = 0;
        $this->init_schema_info();
    }

    /**
     * Retrieve the domain name of this schema.
     *
     * @return string domain name
     */
    public function get_domain()
    {
        return $this->domain;
    }

    /**
     * Initialize the current schema version.
     */
    private function init_schema_info ()
    {
        $query = "SELECT version FROM schema_version WHERE domain = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$this->domain]);
        $this->version = (int) $statement->fetchColumn();
    }

    /**
     * Retrieve the current schema version.
     *
     * @return int schema version
     */
    public function get()
    {
        return $this->version;
    }

    /**
     * Set the current schema version.
     *
     * @param int $version new schema version
     */
    public function set($version)
    {
        $this->version = (int) $version;

        $query = "INSERT INTO schema_version (domain, version)
                  VALUES (?, ?)
                  ON DUPLICATE KEY UPDATE version = VALUES(version)";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([
            $this->domain,
            $this->version
        ]);
        NotificationCenter::postNotification(
            'SchemaVersionDidUpdate',
            $this->domain,
            $version
        );
    }
}
