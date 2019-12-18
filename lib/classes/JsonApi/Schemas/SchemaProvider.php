<?php

namespace JsonApi\Schemas;

use Neomerx\JsonApi\Contracts\Schema\ContainerInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaFactoryInterface;

abstract class SchemaProvider extends \Neomerx\JsonApi\Schema\SchemaProvider
{
    private $schemaContainer;
    private $diContainer;

    /**
     * @param SchemaFactoryInterface $factory
     * @param ContainerInterface     $factory
     */
    public function __construct(SchemaFactoryInterface $factory, ContainerInterface $schemaContainer)
    {
        parent::__construct($factory);

        $this->schemaContainer = $schemaContainer;
        $this->diContainer = $factory->getDependencyInjectionContainer();
    }

    /**
     * Return the set schema container.
     *
     * @return ContainerInterface the schema container
     */
    protected function getSchemaContainer()
    {
        return $this->schemaContainer;
    }

    public function getDiContainer()
    {
        return $this->diContainer;
    }
}
