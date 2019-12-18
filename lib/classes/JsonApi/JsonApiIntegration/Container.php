<?php

namespace JsonApi\JsonApiIntegration;

class Container extends \Neomerx\JsonApi\Schema\Container
{
    /**
     * Überprüft nicht nur, ob es ein mapping zum $type gibt, sondern
     * sucht sonst auch nach mappings der Oberklassen bzw. Interfaces.
     *
     * @param string $type
     *
     * @return bool
     */
    protected function hasProviderMapping($type)
    {
        if (parent::hasProviderMapping($type)) {
            return true;
        }

        foreach ($this->getParentClassesAndInterfaces($type) as $class) {
            if (parent::hasProviderMapping($class)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Liefert nicht nur direkte Treffer aus den provider mappings
     * sondern schaut falls nötig auch nach Oberklassen bzw. Interfaces.
     *
     * @param string $type
     *
     * @return mixed
     */
    protected function getProviderMapping($type)
    {
        $providerMapping = $this->getProviderMappings();

        if (isset($providerMapping[$type])) {
            return $providerMapping[$type];
        }

        foreach ($this->getParentClassesAndInterfaces($type) as $class) {
            if (isset($providerMapping[$class])) {
                return $providerMapping[$class];
            }
        }

        return null;
    }

    private function getParentClassesAndInterfaces($type)
    {
        return class_exists($type) ? @class_parents($type) + @class_implements($type) : [];
    }

    /**
     * @deprecated use `createSchemaFromCallable` method instead
     *
     * @param Closure $closure
     *
     * @return SchemaProviderInterface
     */
    protected function createSchemaFromClosure(\Closure $closure)
    {
        $schema = $closure($this->getFactory(), $this);

        return $schema;
    }

    /**
     * @param callable $callable
     *
     * @return SchemaProviderInterface
     */
    protected function createSchemaFromCallable(callable $callable)
    {
        $schema = $callable instanceof \Closure ?
                $this->createSchemaFromClosure($callable) : call_user_func($callable, $this->getFactory(), $this);

        return $schema;
    }

    /**
     * @param string $className
     *
     * @return SchemaProviderInterface
     */
    protected function createSchemaFromClassName($className)
    {
        $schema = new $className($this->getFactory(), $this);

        return $schema;
    }
}
