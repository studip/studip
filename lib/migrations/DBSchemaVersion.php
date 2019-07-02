<?php
/**
 * DBSchemaVersion.php - database backed schema versions
 *
 * Implementation of SchemaVersion interface using a database table.
 *
 * @author    Elmar Ludwig
 * @copyright 2007 Elmar Ludwig
 * @license   GPL2 or any later version
 * @package   migrations
 */
class DBSchemaVersion implements SchemaVersion
{
    /**
     * domain name of schema version
     *
     * @var string
     */
    private $domain;

    /**
     * schema versions
     *
     * @var array
     */
    private $versions = [];

    /**
     * Initialize a new DBSchemaVersion for a given domain.
     * The default domain name is 'studip'.
     *
     * @param string $domain domain name (optional)
     */
    public function __construct($domain = 'studip')
    {
        $this->domain = $domain;
        $this->initSchemaInfo();
    }

    /**
     * Retrieve the domain name of this schema.
     *
     * @return string domain name
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Initialize the current schema version.
     */
    private function initSchemaInfo()
    {
        try {
            $query = "SELECT version FROM schema_versions WHERE domain = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$this->domain]);
            $this->versions = $statement->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            $query = "SELECT version FROM schema_version WHERE domain = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$this->domain]);
            $this->versions = range(1, $statement->fetchColumn());
        }
    }

    /**
     * Retrieve the current schema version.
     *
     * @return int schema version
     */
    public function get()
    {
        return $this->versions ? max($this->versions) : 0;
    }

    /**
     * Returns whether the given version is already present for the given
     * domain.
     *
     * @param  int $version Version number
     * @return bool
     */
    public function contains($version)
    {
        return in_array($version, $this->versions);
    }

    /**
     * Set the current schema version.
     *
     * @param int $version new schema version
     */
    public function add($version)
    {
        $version = (int) $version;

        try {
            $query = "INSERT INTO `schema_versions` (`domain`, `version`)
                      VALUES (?, ?)";
            DBManager::get()->execute($query, [
                $this->domain,
                $version,
            ]);
        } catch (PDOException $e) {
            $query = "UPDATE `schema_version`
                      SET `version` = ?
                      WHERE `domain` = ?";;
            DBManager::get()->execute($query, [
                $version,
                $this->domain,
            ]);
        }
        NotificationCenter::postNotification(
            'SchemaVersionDidUpdate',
            $this->domain,
            $version
        );
    }

    /**
     * Removes a schema version.
     *
     * @param int $version schema version to remove
     */
    public function remove($version)
    {
        $version = (int) $version;

        try {
            $query = "DELETE FROM `schema_versions`
                      WHERE `domain` = ? AND `version` = ?";
            DBManager::get()->execute($query, [
                $this->domain,
                $version
            ]);
        } catch (PDOException $e) {
            $query = "UPDATE `schema_version`
                      SET `version` = ?
                      WHERE `domain` = ?";
            DBManager::get()->execute($query, [
                $version,
                $this->domain,
            ]);
        }
        NotificationCenter::postNotification(
            'SchemaVersionDidDelete',
            $this->domain,
            $version
        );
    }
}
