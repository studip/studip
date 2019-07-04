<?php
/**
 * MigrationTest.php - unit tests for the migrations
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 */

class MigrationTest extends PHPUnit_Framework_TestCase
{
    protected $migrator;
    protected $before = null;

    public function setUp()
    {
        $this->before = isset($GLOBALS['CACHING_ENABLE'])
                      ? $GLOBALS['CACHING_ENABLE']
                      : null;
        $GLOBALS['CACHING_ENABLE'] = false;

        require_once 'lib/classes/StudipCache.class.php';
        require_once 'lib/classes/StudipNullCache.class.php';
        require_once 'lib/classes/StudipCacheFactory.class.php';
        require_once 'lib/models/SimpleORMap.class.php';

        require_once 'lib/migrations/Migration.php';
        require_once 'lib/migrations/Migrator.php';
        require_once 'lib/migrations/SchemaVersion.php';
    }

    public function tearDown()
    {
        if ($this->before !== null) {
            $GLOBALS['CACHING_ENABLE'] = $this->before;
        } else {
            unset($GLOBALS['CACHING_ENABLE']);
        }
    }

    private function getSchemaVersion()
    {
        return new class() implements SchemaVersion
        {
            private $versions = [];

            public function get()
            {
                return count($this->versions) > 0 ? max($this->versions) : 0;
            }

            public function contains($version)
            {
                return in_array($version, $this->versions);
            }

            public function add($version)
            {
                if (!$this->contains($version)) {
                    $this->versions[] = $version;
                }
            }

            public function remove($version)
            {
                if ($this->contains($version)) {
                    $this->versions = array_diff($this->versions, [$version]);
                }
            }
        };
    }

    private function getMigrator($schema_version = null)
    {
        return new Migrator(
            __DIR__ . '/test-migrations',
            $schema_version ?: $this->getSchemaVersion()
        );
    }

    public function testSchemaVersion()
    {
        $schema_version = $this->getSchemaVersion();
        $this->assertSame(0, $schema_version->get());

        $schema_version->add(1);
        $this->assertTrue($schema_version->contains(1));
        $this->assertSame(1, $schema_version->get());

        $schema_version->add(2);
        $this->assertTrue($schema_version->contains(2));
        $this->assertSame(2, $schema_version->get());

        $schema_version->remove(1);
        $this->assertFalse($schema_version->contains(1));
        $this->assertSame(2, $schema_version->get());
    }

    public function testRelevance()
    {
        $migrator = $this->getMigrator();

        $relevant = $migrator->relevantMigrations(null);
        $this->assertSame(4, count($relevant));

        $migrator->migrateTo(10);

        $relevant = $migrator->relevantMigrations(null);
        $this->assertSame(1, count($relevant));
    }

    public function testMigrationUp()
    {
        $schema_version = $this->getSchemaVersion();
        $migrator = $this->getMigrator($schema_version);
        $migrator->migrateTo(null);
        $this->assertSame(20190417, $schema_version->get());
        $this->assertSame(0, count($migrator->relevantMigrations(null)));

        return $schema_version;
    }

    /**
     * @depends testMigrationUp
     */
    public function testMigrationDown($schema_version)
    {
        $migrator = $this->getMigrator($schema_version);
        $migrator->migrateTo(0);
        $this->assertSame(0, $schema_version->get());
        $this->assertSame(4, count($migrator->relevantMigrations(null)));
    }

    public function testGaps()
    {
        $schema_version = $this->getSchemaVersion();
        $schema_version->add(2);
        $schema_version->add(10);

        $migrator = $this->getMigrator($schema_version);

        $relevant = $migrator->relevantMigrations(null);
        $this->assertSame(2, count($relevant));
        $this->assertEquals([1, 20190417], array_keys($relevant));
    }
}
