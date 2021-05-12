<?php

namespace Courseware\BlockTypes;

use Opis\JsonSchema\Schema;

/**
 * This class represents the content of a Courseware image map block.
 *
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.0
 */
class ImageMap extends BlockType
{
    public static function getType(): string
    {
        return 'image-map';
    }

    public static function getTitle(): string
    {
        return _('Verweissensitive Grafik');
    }

    public static function getDescription(): string
    {
        return _('Beliebige Bereiche auf einem Bild lassen sich verlinken.');
    }

    public function initialPayload(): array
    {
        return [
            'file_id' => '',
            'shapes' => [],
        ];
    }

    /**
     * get all files related to this bloc.
     *
     * @return \FileRef[] list of file references realted to this block
     */
    public function getFiles(): array
    {
        $payload = $this->getPayload();

        $files = [];

        if ($payload['file_id']) {
            $files[] = \FileRef::find($payload['file_id']);
        }

        return $files;
    }

    public function copyPayload(string $rangeId = ''): array
    {
        $payload = $this->getPayload();

        if ('' != $payload['file_id']) {
            $payload['file_id'] = $this->copyFileById($payload['file_id'], $rangeId);
        }

        return $payload;
    }

    public static function getJsonSchema(): Schema
    {
        $schemaFile = __DIR__.'/ImageMap.json';

        return Schema::fromJsonString(file_get_contents($schemaFile));
    }

    public static function getCategories(): array
    {
        return ['interaction'];
    }

    public static function getContentTypes(): array
    {
        return ['image', 'link'];
    }

    public static function getFileTypes(): array
    {
        return ['image'];
    }
}
