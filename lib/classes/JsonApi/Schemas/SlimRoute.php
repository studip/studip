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
            'methods' => studip_utf8encode($route->getMethods()),
            'pattern' => studip_utf8encode($route->getPattern()),
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
