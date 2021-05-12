<?php

namespace Courseware\BlockTypes;

use Opis\JsonSchema\Schema;

/**
 * This class represents the content of a Courseware chart block.
 *
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.0
 */
class Chart extends BlockType
{
    public static function getType(): string
    {
        return 'chart';
    }

    public static function getTitle(): string
    {
        return _('Diagramm');
    }

    public static function getDescription(): string
    {
        return _('Präsentiert Datensätze in einem Diagramm.');
    }

    public function initialPayload(): array
    {
        return [
            'content' => [['label' => '', 'value' => '0', 'color' => 'blue']],
            'label' => '',
            'type' => 'bar',
        ];
    }

    public static function getJsonSchema(): Schema
    {
        $schemaFile = __DIR__.'/Chart.json';

        return Schema::fromJsonString(file_get_contents($schemaFile));
    }

    public static function getCategories(): array
    {
        return ['multimedia', 'layout'];
    }

    public static function getContentTypes(): array
    {
        return ['data'];
    }

    public static function getFileTypes(): array
    {
        return [];
    }
}
