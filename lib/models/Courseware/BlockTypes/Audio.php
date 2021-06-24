<?php

namespace Courseware\BlockTypes;

use Opis\JsonSchema\Schema;

/**
 * This class represents the content of a Courseware audio block.
 *
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.0
 */
class Audio extends BlockType
{
    public static function getType(): string
    {
        return 'audio';
    }

    public static function getTitle(): string
    {
        return _('Audio');
    }

    public static function getDescription(): string
    {
        return _('Spielt eine Audiodatei aus dem Dateibereich oder von einer URL ab.');
    }

    public function initialPayload(): array
    {
        return [
            'title' => 'Audio',
            'source' => 'studip_folder',
            'file_id' => '',
            'folder_id' => '',
            'web_url' => '',
            'recorder_enabled' => 'false'
        ];
    }

    public static function getJsonSchema(): Schema
    {
        $schemaFile = __DIR__.'/Audio.json';

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
        if (!$payload['file_id'] && !$payload['folder_id']) {
            return [];
        }

        if ($payload['file_id']) {
            $files = [];

            if ($fileRef = \FileRef::find($payload['file_id'])) {
                $files[] = $fileRef;
            }

            return $files;
        } else {
            return \FileRef::findByFolder_id($payload['folder_id']);
        }
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
        return ['audio'];
    }

    public static function getFileTypes(): array
    {
        return ['audio'];
    }
}
