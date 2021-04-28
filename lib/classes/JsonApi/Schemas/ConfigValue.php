<?php

namespace JsonApi\Schemas;

class ConfigValue extends SchemaProvider
{
    const TYPE = 'config-values';

    protected $resourceType = self::TYPE;

    public function getId($resource)
    {
        return join('_', [$resource['range_id'], $resource['field']]);
    }

    public function getAttributes($resource)
    {
        $i18nAwareCast = function ($mixed) use ($resource) {
            return 'i18n' === $resource->entry['type'] ? (string) $mixed : $mixed;
        };

        return [
            'field' => $resource['field'],
            'value' => $i18nAwareCast($resource->getTypedValue()),
            'field-type' => $resource->entry['type'],
            'comment' => $resource['comment'],
            'default-value' => $i18nAwareCast($resource->getTypedDefaultValue()),
            'mkdate' => date('c', $resource['mkdate']),
            'chdate' => date('c', $resource['chdate']),
        ];
    }
}
