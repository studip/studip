<?php

namespace Courseware\BlockTypes;

use Opis\JsonSchema\Schema;

/**
 * This class represents the content of a Courseware typewriter block.
 *
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.0
 */
class Typewriter extends BlockType
{
    public static function getType(): string
    {
        return 'typewriter';
    }

    public static function getTitle(): string
    {
        return _('Schreibmaschine');
    }

    public static function getDescription(): string
    {
        return _('Der Text erscheint Zeichen für Zeichen.');
    }

    public function initialPayload(): array
    {
        return [
            'text' => '',
            'speed' => '1',
            'font' => 'font-default',
            'size' => 'size-default',
        ];
    }

    public static function getJsonSchema(): Schema
    {
        $schemaFile = __DIR__.'/Typewriter.json';

        return Schema::fromJsonString(file_get_contents($schemaFile));
    }

    public static function getCategories(): array
    {
        return ['text'];
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
