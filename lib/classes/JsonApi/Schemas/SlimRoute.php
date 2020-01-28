<?php

namespace JsonApi\Schemas;

class SlimRoute extends SchemaProvider
{
    const TYPE = 'slim-routes';

    protected $resourceType = self::TYPE;

    public function getId($route)
    {
        return $route->getIdentifier();
    }

    public function getAttributes($route)
    {
        return [
            'methods' => $route->getMethods(),
            'pattern' => $route->getPattern(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getResourceLinks($resource)
    {
        return [];
    }
}
