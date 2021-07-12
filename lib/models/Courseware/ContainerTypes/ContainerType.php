<?php

namespace Courseware\ContainerTypes;

use Courseware\CoursewarePlugin;
use Opis\JsonSchema\Schema;
use Opis\JsonSchema\Validator;

/**
 * This class represents the content of a Courseware container stored in payload.
 *
 * @author  Marcus Eibrink-Lunzenauer <lunzenauer@elan-ev.de>
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.0
 */
abstract class ContainerType
{
    /**
     * Returns the initial payload of every instance of this type of container.
     *
     * @return array<mixed> the initial payload of an instance of this type of container
     */
    abstract public function initialPayload(): array;

    /**
     * Returns the JSON schema which is used to validate the payload of
     * instances of this type of container.
     *
     * @return Schema the JSON schema to be used
     */
    abstract public static function getJsonSchema(): Schema;

    /**
     * Returns a short string describing this type of container.
     *
     * @return string the short string describing this type
     */
    abstract public static function getType(): string;

    /**
     * Returns the title of this type of containers suitable to display it to the user.
     *
     * @return string the title of this type of containers
     */
    abstract public static function getTitle(): string;

    /**
     * Returns the description of this type of containers suitable to display it to the user.
     *
     * @return string the description of this type of containers
     */
    abstract public static function getDescription(): string;

    /**
     * Adds a block id to payload of the container
     *
     *  @param /Courseware/Block $block - block to be added to the container
     */
    abstract public function addBlock($block): void;

    /**
     * Returns all known types of containers: core types and plugin types as well.
     *
     * @return array<string> a list of all known types of containers;
     *                       each one a fully qualified class name
     */
    public static function getContainerTypes(): array
    {
        $containerTypes = [AccordionContainer::class, ListContainer::class, TabsContainer::class];

        try {
            foreach (\PluginEngine::getPlugins(CoursewarePlugin::class) as $plugin) {
                $containerTypes = $plugin->registerContainerTypes($containerTypes);
            }
        } catch (\Exception $e) {
            // there is nothing we can do here other than absorbing exceptions
        }

        return $containerTypes;
    }

    /**
     * @param string $containerType a short string describing a type of container; see `getType`
     *
     * @return bool true, if the given type of container is valid; false otherwise
     */
    public static function isContainerType(string $containerType): bool
    {
        return null !== self::findContainerType($containerType);
    }

    /**
     * Returns the classname of a container type whose `type` equals the given one.
     *
     * @param string $containerType a short string describing a type of container; see `getType`
     *
     * @return ?string either the classname if the given type was valid; null otherwise
     */
    public static function findContainerType(string $containerType): ?string
    {
        static $mapping;

        // memorize mapping of types to classes
        if (!$mapping) {
            $mapping = array_reduce(
                self::getContainerTypes(),
                function ($memo, $typeClass) {
                    $memo[$typeClass::getType()] = $typeClass;

                    return $memo;
                },
                []
            );
        }

        foreach ($mapping as $class) {
            if ($class::getType() === $containerType) {
                return $class;
            }
        }

        return null;
    }

    /**
     * Creates an instance of ContainerType for a given container.
     *
     * @param \Courseware\Container $container the container whose ContainerType is returned
     *
     * @return ContainerType the ContainerType associated with the given container
     */
    public static function factory(\Courseware\Container $container): ContainerType
    {
        if (!($class = self::findContainerType($container['container_type']))) {
            // TODO: Hier müsste es eine weniger allgemeine Exception geben.
            throw new \RuntimeException('Invalid `container_type` attribute in database.');
        }

        return new $class($container);
    }

    /**
     * Validates a given payload according to the JSON schema of this type of container.
     *
     * @param mixed $payload the payload to be validated
     *
     * @return bool true, if the given payload is valid; false otherwise
     */
    public function validatePayload($payload): bool
    {
        $schema = static::getJsonSchema();
        $validator = new Validator();
        $result = $validator->schemaValidation($payload, $schema);

        return $result->isValid();
    }

    /** @var \Courseware\Container */
    protected $container;

    /**
     * @param \Courseware\Container $container the container associated to this type
     */
    public function __construct(\Courseware\Container $container)
    {
        $this->container = $container;
    }

    /**
     * Returns the decoded payload of the block associated with this instance.
     *
     * @return mixed the decoded payload
     */
    public function getPayload()
    {
        $decoded = $this->decodePayloadString($this->container['payload']);

        return $decoded;
    }

    /**
     * Encodes the payload and sets it in the associated block.
     *
     * @param mixed $payload the payload to be encoded
     */
    public function setPayload($payload): void
    {
        $this->container['payload'] = null === $payload ? '' : json_encode($payload, true);
    }

    // TODO: (tgloeggl) DocBlock ergänzen
    public function copyPayload(array $block_map = []): array
    {
        $payload = $this->getPayload();

        foreach ($payload['sections'] as &$section) {
            foreach ($section['blocks'] as &$block) {
                $block = $block_map[$block];
            }
        }

        return $payload;
    }

    /**
     * Decode a given payload.
     *
     * @param string $payload the payload to be decoded
     *
     * @return mixed the decoded payload
     */
    protected function decodePayloadString(string $payload)
    {
        if ('' === $payload) {
            $decoded = $this->initialPayload();
        } else {
            $decoded = json_decode($payload, true);
            if (JSON_ERROR_NONE !== json_last_error()) {
                throw new \RuntimeException('TODO');
            }
        }

        return $decoded;
    }

    /**
     * Returns a containers width as a description.
     *
     * @return string the description
     */
    public function getContainerWidth(): string
    {
        $width = [
            'full' => _('volle Breite'),
            'half' => _('halbe Breite'),
            'half-center' => _('halbe Breite (zentriert)'),
        ];
        $payload = $this->getPayload();
        if (array_key_exists($payload['colspan'], $width)) {
            return $width[$payload['colspan']];
        } else {
            return _('unbekannter Courseware-Container');
        }
    }
}
