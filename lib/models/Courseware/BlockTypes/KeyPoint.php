<?php

namespace Courseware\BlockTypes;

use Opis\JsonSchema\Schema;

/**
 * This class represents the content of a Courseware key point block.
 *
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.0
 */
class KeyPoint extends BlockType
{
    public static function getType(): string
    {
        return 'key-point';
    }

    public static function getTitle(): string
    {
        return _('Merksatz');
    }

    public static function getDescription(): string
    {
        return _('Erzeugt einen Merksatz mit Icon und Rahmen.');
    }

    public function initialPayload(): array
    {
        return [
            'text' => 'Merksatz',
            'icon' => 'courseware',
            'color' => 'blue',
        ];
    }

    public static function getJsonSchema(): Schema
    {
        $schemaFile = __DIR__.'/KeyPoint.json';

        return Schema::fromJsonString(file_get_contents($schemaFile));
    }

    public static function getCategories(): array
    {
        return ['text', 'layout'];
    }

    public static function getContentTypes(): array
    {
        return ['text'];
    }

    public static function getFileTypes(): array
    {
        return [];
    }
}
