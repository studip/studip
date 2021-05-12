<?php

namespace Courseware\BlockTypes;

use Opis\JsonSchema\Schema;

/**
 * This class represents the content of a Courseware text block.
 *
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.0
 */
class Text extends BlockType
{
    public static function getType(): string
    {
        return 'text';
    }

    public static function getTitle(): string
    {
        return _('Text');
    }

    public static function getDescription(): string
    {
        return _('Erstellen von Inhalten mit dem WYSIWYG-Editor.');
    }

    public function initialPayload(): array
    {
        return ['text' => ''];
    }

    public static function getJsonSchema(): Schema
    {
        $schemaFile = __DIR__.'/Text.json';

        return Schema::fromJsonString(file_get_contents($schemaFile));
    }

    public static function getCategories(): array
    {
        return ['basis', 'text'];
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
