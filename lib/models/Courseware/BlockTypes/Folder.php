<?php

namespace Courseware\BlockTypes;

use Opis\JsonSchema\Schema;

/**
 * This class represents the content of a Courseware folder block.
 *
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.0
 */
class Folder extends BlockType
{
    public static function getType(): string
    {
        return 'folder';
    }

    public static function getTitle(): string
    {
        return _('Dateiordner');
    }

    public static function getDescription(): string
    {
        return _('Stellt einen Ordner aus dem Dateibereich zur Verfügung.');
    }

    public function initialPayload(): array
    {
        return [
            'type' => '', // store folder type to detect changes
            'folder_id' => '',
            'title' => '',
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

        return \FileRef::findByFolder_id($payload['folder_id']);
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
        $schemaFile = __DIR__.'/Folder.json';

        return Schema::fromJsonString(file_get_contents($schemaFile));
    }

    public static function getCategories(): array
    {
        return ['files'];
    }

    public static function getContentTypes(): array
    {
        return ['files'];
    }

    public static function getFileTypes(): array
    {
        return ['all'];
    }
}
