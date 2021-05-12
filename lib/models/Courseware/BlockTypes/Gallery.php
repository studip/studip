<?php

namespace Courseware\BlockTypes;

use Opis\JsonSchema\Schema;

/**
 * This class represents the content of a Courseware gallery block.
 *
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.0
 */
class Gallery extends BlockType
{
    public static function getType(): string
    {
        return 'gallery';
    }

    public static function getTitle(): string
    {
        return _('Galerie');
    }

    public static function getDescription(): string
    {
        return _('Bilder aus einem Ordner im Dateibereich zeigen.');
    }

    public function initialPayload(): array
    {
        return [
            'folder_id' => '',
            'autoplay' => 'false',
            'autoplay_timer' => '2',
            'nav' => 'true',
            'height' => '610',
            'show_filenames' => 'true',
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
        if (!$payload['folder_id']) {
            return [];
        }

        $files = [];

        if ($fileRefs = \FileRef::findByFolder_id($payload['folder_id'])) {
            $files = $fileRefs;
        }

        return $files;
    }

    public function copyPayload(string $rangeId = ''): array
    {
        $payload = $this->getPayload();
        if ('' != $payload['folder_id']) {
            $payload['folder_id'] = $this->copyFolderById($payload['folder_id'], $rangeId);
        }

        return $payload;
    }

    public static function getJsonSchema(): Schema
    {
        $schemaFile = __DIR__.'/Gallery.json';

        return Schema::fromJsonString(file_get_contents($schemaFile));
    }

    public static function getCategories(): array
    {
        return ['multimedia'];
    }

    public static function getContentTypes(): array
    {
        return ['image'];
    }

    public static function getFileTypes(): array
    {
        return ['image'];
    }
}
