<?
/**
 * Template documentation:
 * This template expects the following parameters to be present:
 *
 * - $defined_properties: An array of ResourcePropertyDefinition objects.
 * - $property_data: An array with the property states where the array keys
 *   represent the property-IDs and the array items represent the corresponding
 *   property values.
 */
?>
<? if ($grouped_defined_properties): ?>
    <? foreach ($grouped_defined_properties as $group_name => $properties): ?>
        <h2><?= htmlReady($group_name) ?></h2>
        <? foreach ($properties as $property): ?>
            <?= $property->toHtmlInput(
                $property_data[$property->id],
                '',
                true,
                '',
                false
            ) ?>
        <? endforeach ?>
    <? endforeach ?>
<? endif ?>
