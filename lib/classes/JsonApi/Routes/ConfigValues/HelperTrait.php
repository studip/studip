<?php

namespace JsonApi\Routes\ConfigValues;

use JsonApi\Errors\RecordNotFoundException;

trait HelperTrait
{
    private function generateId(\ConfigValue $resource): string
    {
        return join('_', [$resource['range_id'], $resource['field']]);
    }

    private function parseId(string $userConfigId): array
    {
        return explode('_', $userConfigId, 2);
    }

    private function getRange(string $rangeId): ?\Range
    {
        if ('studip' === $rangeId) {
            return null;
        }

        if (!($range = \RangeFactory::find($rangeId))) {
            throw new RecordNotFoundException();
        }

        return $range;
    }

    private function findOrFakeConfigValue(?\Range $range, string $field)
    {
        // first search optimistically for this config value
        if ($configValue = \ConfigValue::find([$field, $range->id])) {
            return $configValue;
        }

        // try to find a default config entry
        if (!($configEntry = \ConfigEntry::find($field))) {
            throw new RecordNotFoundException();
        }

        return \ConfigValue::build([
            'field' => $field,
            'range_id' => $range->id,
            'value' => $configEntry->value,
            'mkdate' => $configEntry->mkdate,
            'chdate' => $configEntry->chdate,
        ]);
    }
}
