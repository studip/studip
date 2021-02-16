<?php
/**
 * Object builder class
 *
 * This class can be used to serialize/unserialize objects without using
 * PHP's native functions. This should be used in contexts where the
 * serialized data is exposed to the user.
 *
 * PHP's native implementation has proven to be rather insecure in the
 * past and since json_encode() cannot handle objects, we need a less
 * complex solution that has less magic going on in the background.
 *
 * Like unserialize, the magic methods __sleep() and __wakeup() are
 * supported. Accordingly, objects are created without invoking the
 * constructor.
 *
 * @author  Jan-Hendrik Willms
 * @license GPL2 or any later version
 */
class ObjectBuilder
{
    // Magic string constant used as array index that idefinties
    // a serialized object
    const OBJECT_IDENTIFIER = '__SERIALIZED_CLASS__';

    /**
     * Restores an object that has previously been converted to an array.
     * Like unserialize, an expected class may be passed and the method will
     * throw an exception when the created object is not of that or a derived
     * type.
     *
     * Object data may either be passed as an array or as a json encoded
     * string which will be decoded prior to object creation.
     *
     * @param mixed $data           Associative array or json encoded string
     * @param mixed $expected_class Expected class name of objects (optional)
     * @return mixed Object of almost arbitrary type
     *
     * @throws InvalidArgumentException when either the data contains no object
     * @throws UnexpectedValueException when the object is not of the expected
     *                                  type
     */
    public static function build($data, $expected_class = null)
    {
        // Decode data string to array if neccessary
        if (!is_array($data)) {
            $data = json_decode($data, true);
        }

        // Check if we actually have an object
        if (!self::isSerializedObject($data)) {
            throw new InvalidArgumentException(
                "Object can not be built since provided data is invalid"
            );
        }

        // Grab class name
        $class_name = $data[self::OBJECT_IDENTIFIER];

        // Check if object is of expected type
        if ($expected_class !== null && !is_a($class_name, $expected_class, true)) {
            throw new UnexpectedValueException(
                "Object can not be built since it's not of type '{$expected_class}."
            );
        }

        // Create reflection class
        $reflected = new ReflectionClass($class_name);
        $object = $reflected->newInstanceWithoutConstructor();

        // Iterate over properties and set them
        $properties = $reflected->getProperties();
        foreach ($properties as $property) {
            // No value provided -> leave default
            if (!array_key_exists($property->name, $data)) {
                continue;
            }

            // Recursively extract objects from value
            $value = $data[$property->name];
            if (self::isSerializedObject($value)) {
                $value = self::build($value);
            } elseif (is_array($value)) {
                $array_walker = function (&$item) use (&$array_walker) {
                    if (self::isSerializedObject($item)) {
                        $item = self::build($item);
                    } elseif (is_array($item)) {
                        array_walk($item, $array_walker);
                    }
                };
                array_walk($value, $array_walker);
            }

            // Enable access to property and set value
            $property->setAccessible(true);
            $property->setValue($object, $value);
        }

        // Call potential magic __wakeup() method
        if (method_exists($object, '__wakeup')) {
            $object->__wakeup();
        }

        return $object;
    }

    /**
     * Restores a collection of objects that have previously been converted
     * to a arrays. This essentially iterates over the passed array and
     * invokes build() on each item.
     *
     * @param array $array Associative array or json encoded string
     * @param mixed $expected_class Expected class name of objects (optional)
     * @return array as collection of objects
     * @throws InvalidArgumentException when either the data contains no
     *         objects or an object is not of the expected type
     * @see ObjectBuilder::build
     */
    public static function buildMany($array, $expected_class = null)
    {
        if ($array === null || !is_array($array)) {
            throw new InvalidArgumentException(
                "Objects can not be built since provided data is invalid"
            );
        }

        $result = [];
        foreach (array_filter($array) as $index => $row) {
            $result[$index] = self::build($row, $expected_class);
        }
        return $result;
    }

    /**
     * Checks whether the passed variable contains an object. A variable must
     * be an array and must contain the magic string constant as an array index
     * to be considered an object.
     *
     * @param mixed $variable Variable to check
     */
    public static function isSerializedObject($variable)
    {
        return is_array($variable) && array_key_exists(self::OBJECT_IDENTIFIER, $variable);
    }

    /**
     * Returns all properties (public, protected and private) from given
     * object as associative array as well as the information about the class
     * itself. Be aware that values will only be returned if they differ from
     * the default value. This should ensure a small footprint.
     *
     * @param mixed $object Arbitrary object
     * @return array containing the serialized object
     * @throws InvalidArgumentException when given object is actually not an
     *         object
     */
    public static function export($object)
    {
        // Check if variable is actually an object
        if (!is_object($object)) {
            throw new InvalidArgumentException("ObjectBuilder can only convert objects");
        }

        // Create reflection class and get properties and defaults
        $reflection = new ReflectionClass(get_class($object));
        $properties  = $reflection->getProperties();
        $defaults    = $reflection->getDefaultProperties();
        $sleep_vars  = method_exists($object, '__sleep') ? $object->__sleep() : false;

        // Create resulting array
        $variables = [];
        foreach ($properties as $property) {
            // Check if the variable should be serialized
            if ($sleep_vars && !in_array($property->name, $sleep_vars)) {
                continue;
            }

            // Allow access to property and get value
            $property->setAccessible(true);
            $value = $property->getValue($object);

            // Check if value differs from default
            if (isset($defaults[$property->name]) && $value === $defaults[$property->name]) {
                continue;
            }

            // Recursively convert (nested) objects
            if (is_object($value)) {
                $value = self::export($value);
            } elseif (is_array($value)) {
                array_walk_recursive($value,
                    function (&$item) {
                        if (is_object($item)) {
                            $item = self::export($item);
                        }
                    }
                );
            }

            // Store serialized value
            $variables[$property->name] = $value;
        }

        // Store class information
        $variables[self::OBJECT_IDENTIFIER] = get_class($object);

        return $variables;
    }

    /**
     * Returns the exported object as a JSON encoded string.
     * This is just a convenience method that saves you from wrapping the
     * call to export() in json_encode() every single time.
     *
     * @param mixed $object Arbitrary object
     * @return string containing the serialized object as a JSON string
     * @throws InvalidArgumentException when given object is actually not an
     *         object
     */
    public static function exportAsJson($object)
    {
        return json_encode(self::export($object));
    }
}
