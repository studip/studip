<?php

namespace Courseware\BlockTypes;

use Opis\JsonSchema\Schema;

/**
 * This class represents the content of a Courseware link block.
 *
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.0
 */
class Link extends BlockType
{
    public static function getType(): string
    {
        return 'link';
    }

    public static function getTitle(): string
    {
        return _('Link');
    }

    public static function getDescription(): string
    {
        return _('Erstellt einen Link innerhalb der Courseware oder auf eine andere Seite.');
    }

    public function initialPayload(): array
    {
        return [
            'type' => '',
            'target' => '',
            'url' => '',
            'title' => '',
        ];
    }

    public static function getJsonSchema(): Schema
    {
        $schemaFile = __DIR__.'/Link.json';

        return Schema::fromJsonString(file_get_contents($schemaFile));
    }

    public static function getCategories(): array
    {
        return ['layout', 'external'];
    }

    public static function getContentTypes(): array
    {
        return ['text', 'layout', 'link'];
    }

    public static function getFileTypes(): array
    {
        return [];
    }
}
