<?php
/**
 * ObjectBuilderTest.php - unit tests for the object builder class
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 */

class ObjectBuilderTest extends \Codeception\Test\Unit
{
    protected $object;
    protected $changed_object;

    protected $another_object;
    protected $another_changed_object;

    public function setUp ()
    {
        require_once 'lib/functions.php';
        require_once 'lib/classes/ObjectBuilder.php';

        $this->object = new ObjectBuilderTestMock();

        $this->changed_object = new ObjectBuilderTestMock();
        $this->changed_object->setFoo('bar');
        $this->changed_object->setBar(23);
        $this->changed_object->setBaz(true);

        $this->another_object = new AnotherObjectBuilderTestMock();

        $this->another_changed_object = new AnotherObjectBuilderTestMock();
        $this->another_changed_object->setFoo(23);
        $this->another_changed_object->setBar('Something');
        $this->another_changed_object->setBaz('else');

        $this->simple_array_definition = [
            ObjectBuilder::OBJECT_IDENTIFIER => 'ObjectBuilderTestMock'
        ];

        $this->another_simple_array_definition = [
            ObjectBuilder::OBJECT_IDENTIFIER => 'AnotherObjectBuilderTestMock'
        ];
    }

    public function testObjectDetection()
    {
        $this->assertFalse(ObjectBuilder::isSerializedObject(null));
        $this->assertFalse(ObjectBuilder::isSerializedObject(false));
        $this->assertFalse(ObjectBuilder::isSerializedObject(23));
        $this->assertFalse(ObjectBuilder::isSerializedObject('string'));
        $this->assertFalse(ObjectBuilder::isSerializedObject($this->object));
        $this->assertFalse(ObjectBuilder::isSerializedObject([]));

        $this->assertTrue(ObjectBuilder::isSerializedObject($this->simple_array_definition));
    }

    public function testEqualityAfterEncodeAndDecode()
    {
        $converted_object = ObjectBuilder::export($this->object);
        $restored_object  = ObjectBuilder::build($converted_object);

        $this->assertEquals($this->object, $restored_object);
    }

    public function testExpectedType()
    {
        $this->assertInstanceOf(
            'ObjectBuilderTestMock',
            ObjectBuilder::build(
                $this->simple_array_definition,
                'ObjectBuilderTestMock'
            )
        );

        // Derived classes
        $this->assertInstanceOf(
            'ObjectBuilderTestMock',
            ObjectBuilder::build(
                $this->another_simple_array_definition,
                'AnotherObjectBuilderTestMock'
            )
        );
    }

    /**
     * @expectedException UnexpectedValueException
     */
    public function testExceptionOnExpectedType()
    {
        $this->assertInstanceOf('ObjectBuilderTestMock', ObjectBuilder::build([
            ObjectBuilder::OBJECT_IDENTIFIER => 'AnotherObjectBuilderTestMock',
        ], 'FooBar'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testExceptionOnNull()
    {
        ObjectBuilder::build(null);
    }

    public function testChangedPublicProperties()
    {
        $object_a = new ObjectBuilderTestMock();
        $object_a->foo = 'bar';

        $this->assertEquals($object_a, ObjectBuilder::build([
            ObjectBuilder::OBJECT_IDENTIFIER => 'ObjectBuilderTestMock',
            'foo' => 'bar',
        ]));
    }

    public function testChangedNonPublicProperties()
    {
        $this->assertEquals($this->changed_object, ObjectBuilder::build([
            ObjectBuilder::OBJECT_IDENTIFIER => 'ObjectBuilderTestMock',
            'foo' => 'bar',
            'bar' => 23,
            'baz' => true,
        ]));
    }

    public function testMinimalFootprint()
    {
        $this->assertEquals(
            ObjectBuilder::export(new ObjectBuilderTestMock()),
            $this->simple_array_definition
        );
    }

    public function testJsonInput()
    {
        $object_from_array = ObjectBuilder::build(
            $this->simple_array_definition
        );
        $object_from_json  = ObjectBuilder::build(
            json_encode($this->simple_array_definition)
        );

        $this->assertEquals($object_from_array, $object_from_json);
    }

    public function testJsonOutput()
    {
        $json = ObjectBuilder::exportAsJson($this->object);

        $this->assertEquals($json, sprintf(
            '{"%s":"ObjectBuilderTestMock"}',
            ObjectBuilder::OBJECT_IDENTIFIER
        ));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidJsonInput()
    {
        ObjectBuilder::build(
            json_encode($this->simple_array_definition) . 'brokenJSON'
        );
    }

    public function testJsonCompatibility()
    {
        $array_before    = ObjectBuilder::export($this->object);
        $json            = json_encode($array_before);
        $array_after     = json_decode($json, true);
        $restored_object = ObjectBuilder::build($array_after);

        $this->assertEquals($this->object, $restored_object);
    }

    public function testSleep()
    {
        $array = ObjectBuilder::export($this->another_changed_object);

        $this->assertArrayHasKey('foo', $array);
        $this->assertEquals(23, $array['foo']);
        // $this->assertArraySubset(['foo' => 23], $array);

        $this->assertArrayNotHasKey('bar', $array);
        $this->assertArrayNotHasKey('baz', $array);
    }

    public function testWakeup()
    {
        $restored_object = ObjectBuilder::build([
            ObjectBuilder::OBJECT_IDENTIFIER => 'AnotherObjectBuilderTestMock',
        ]);

        $this->assertTrue($restored_object->woken_up);
    }

    public function testNestedArray()
    {
        $object = new ObjectBuilderTestMock();
        $object->foo = ['a', 'b', 'c'];

        $restored_object = ObjectBuilder::build(array_merge(
            $this->simple_array_definition,
            ['foo' => ['a', 'b', 'c']]
        ));

        $this->assertEquals($object, $restored_object);
    }

    public function testNestedObject()
    {
        $object = new ObjectBuilderTestMock();
        $object->foo = new ObjectBuilderTestMock();

        $restored_object = ObjectBuilder::build(array_merge(
            $this->simple_array_definition,
            ['foo' => $this->simple_array_definition]
        ));

        $this->assertEquals($object, $restored_object);
    }

    public function testNestedObjectInArray()
    {
        $object = new ObjectBuilderTestMock();
        $object->foo = ['bar' => [new ObjectBuilderTestMock()]];

        $restored_object = ObjectBuilder::build(array_merge(
            $this->simple_array_definition,
            ['foo' => ['bar' => [$this->simple_array_definition]]]
        ));

        $this->assertEquals($object, $restored_object);
    }

    public function testManyObjects()
    {
        $restored = ObjectBuilder::buildMany([
            $this->simple_array_definition,
            $this->another_simple_array_definition,
        ]);

        $this->assertInternalType('array', $restored);
        $this->assertCount(2, $restored);

        $this->assertInstanceOf('ObjectBuilderTestMock', $restored[0]);
        $this->assertInstanceOf('AnotherObjectBuilderTestMock', $restored[1]);
    }
}

class ObjectBuilderTestMock
{
    public $foo = '1';
    protected $bar = 42;
    private $baz = false;

    public function setFoo($foo)
    {
        $this->foo = $foo;
    }

    public function setBar($bar)
    {
        $this->bar = $bar;
    }

    public function setBaz($baz)
    {
        $this->baz = $baz;
    }
}

class AnotherObjectBuilderTestMock extends ObjectBuilderTestMock
{
    public $woken_up = false;

    public function __sleep()
    {
        return ['foo'];
    }

    public function __wakeup()
    {
        $this->woken_up = true;
    }
}

