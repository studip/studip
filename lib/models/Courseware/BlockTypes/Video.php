<?php

namespace Courseware\BlockTypes;

use Opis\JsonSchema\Schema;

/**
 * This class represents the content of a Courseware video block.
 *
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.0
 */
class Video extends BlockType
{
    public static function getType(): string
    {
        return 'video';
    }

    public static function getTitle(): string
    {
        return _('Video');
    }

    public static function getDescription(): string
    {
        return _('Spielt ein Video aus dem Dateibereich oder von einer URL ab.');
    }

    public function initialPayload(): array
    {
        return [
            'title' => 'Video',
            'source' => 'studip',
            'file_id' => '',
            'web_url' => '',
            'aspect' => '169',
            'autoplay' => 'disabled',
            'context_menu' => 'enabled',
        ];
    }

    public static function getJsonSchema(): Schema
    {
        $schemaFile = __DIR__.'/Video.json';

        return Schema::fromJsonString(file_get_contents($schemaFile));
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

    public static function getCategories(): array
    {
        return ['basis', 'multimedia'];
    }

    public static function getContentTypes(): array
    {
        return ['video'];
    }

    public static function getFileTypes(): array
    {
        return ['video'];
    }
}
