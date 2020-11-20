<?php
/**
 * StudipCachedArrayTest.php - unit tests for the StudipCachedArray class
 *
 * @author   Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license  GPL2 or any later version
 *
 * @covers StudipCachedArray
 * @uses StudipMemoryCache
 */

class StudipCachedArrayTest extends \Codeception\Test\Unit
{
    private $cache;

    public function setUp()
    {
        $this->cache = new TestCache();
    }

    private function getCachedArray($partition_by = 1, $encoding = StudipCachedArray::ENCODE_JSON)
    {
        return new StudipCachedArray(
            md5(uniqid(__CLASS__, true)),
            $partition_by,
            $encoding,
            $this->cache
        );
    }

    /**
     * @after
     */
    public function clearCache()
    {
        $this->cache->flush();
    }

    /**
     * @dataProvider JSONStorageProvider
     */
    public function testJSONStorage($key, $value)
    {
        $cache = $this->getCachedArray();

        // Cache should be empty
        $this->assertFalse(isset($cache[$key]));

        // Set value
        $cache[$key] = $value;

        // Immediate response
        $this->assertTrue(isset($cache[$key]));
        $this->assertEquals($value, $cache[$key]);

        // When reading back from cache
        $cache->reset();

        $this->assertTrue(isset($cache[$key]));
        $this->assertEquals($value, $cache[$key]);

        // Remove value
        unset($cache[$key]);
        $this->assertFalse(isset($cache[$key]));

        $cache->reset();

        $this->assertFalse(isset($cache[$key]));
    }

    /**
     * @depends testJSONStorage
     */
    public function testJSONCountable()
    {
        $cache = $this->getCachedArray();

        $this->assertEquals(0, count($cache));

        $cache['foo'] = 'bar';
        $this->assertEquals(1, count($cache));

        unset($cache['foo']);
        $this->assertEquals(0, count($cache));
    }

    /**
     * @depends testJSONCountable
     */
    public function testJSONClear()
    {
        $cache = $this->getCachedArray();

        $count = 100;

        for ($i = 0; $i < $count; $i += 1) {
            $cache[$i] = $i;
        }

        $this->assertEquals($count, count($cache));

        $cache->clear();

        $this->assertEquals(0, count($cache));
    }

    /**
     * @dataProvider SerializedStorageProvider
     */
    public function testSerializedStorage($key, $value)
    {
        $cache = $this->getCachedArray(1, StudipCachedArray::ENCODE_SERIALIZE);

        // Cache should be empty
        $this->assertFalse(isset($cache[$key]));

        // Set value
        $cache[$key] = $value;

        // Immediate response
        $this->assertTrue(isset($cache[$key]));
        $this->assertEquals($value, $cache[$key]);

        // When reading back from cache
        $cache->reset();

        $this->assertTrue(isset($cache[$key]));
        $this->assertEquals($value, $cache[$key]);

        // Remove value
        unset($cache[$key]);
        $this->assertFalse(isset($cache[$key]));

        $cache->reset();

        $this->assertFalse(isset($cache[$key]));
    }

    /**
     * @depends testSerializedStorage
     */
    public function testSerializedCountable()
    {
        $cache = $this->getCachedArray(1, StudipCachedArray::ENCODE_SERIALIZE);

        $this->assertEquals(0, count($cache));

        $cache['foo'] = 'bar';
        $this->assertEquals(1, count($cache));

        unset($cache['foo']);
        $this->assertEquals(0, count($cache));
    }

    /**
     * @depends testSerializedCountable
     */
    public function testSerializedClear()
    {
        $cache = $this->getCachedArray(1, StudipCachedArray::ENCODE_SERIALIZE);

        $count = 100;

        for ($i = 0; $i < $count; $i += 1) {
            $cache[$i] = $i;
        }

        $this->assertEquals($count, count($cache));

        $cache->clear();

        $this->assertEquals(0, count($cache));
    }

    /**
     * This will test the partitioning by a slice of the key with the length 2.
     */
    public function testPartitioningByInt1()
    {
        $cache = $this->getCachedArray();

        $cache['abc'] = 1;
        $cache['acd'] = 1;
        $cache['def'] = 1;

        $this->assertEquals(3, count($this->cache->getCachedData()));
    }

    /**
     * This will test the partitioning by a slice of the key with the length 2.
     */
    public function testPartitioningByInt2()
    {
        $cache = $this->getCachedArray(2);

        $cache['abc'] = 1;
        $cache['acd'] = 1;
        $cache['def'] = 1;

        $this->assertEquals(4, count($this->cache->getCachedData()));
    }

    /**
     * This will test the partitioning by a user defined function that
     * always returns the same string.
     */
    public function testPartitioningByFunction()
    {
        $cache = $this->getCachedArray(function () {
            return 'test';
        });

        $cache['abc'] = 1;
        $cache['acd'] = 1;
        $cache['def'] = 1;

        $this->assertEquals(2, count($this->cache->getCachedData()));
    }

    /**
     * This will test the getArrayCopy() method
     */
    public function testGetArrayCopy()
    {
        $data = [23 => 42, 42 => 23];

        $cache = $this->getCachedArray();
        foreach ($data as $key => $value) {
            $cache[$key] = $value;
        }

        $this->assertEquals($data, $cache->getArrayCopy());
    }

    public function JSONStorageProvider(): array
    {
        return [
            'null'   => [1, null],
            'true'   => [2, true],
            'false'  => [3, false],
            'int'    => [4, 42],
            'string' => ['string', 'bar'],
            'array'  => ['array', ['foo']],
        ];
    }

    public function SerializedStorageProvider(): array
    {
        return array_merge(
            $this->JSONStorageProvider(),
            ['object' => ['object', new TestClass()]]
        );
    }
}

// Extend memory cache so we will gain access to the internal data
class TestCache extends StudipMemoryCache
{
    public function getCachedData()
    {
        return $this->memory_cache;
    }
}

// Simple test class
class TestClass
{
    private $foo = 42;
}
