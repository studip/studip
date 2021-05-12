<?php

namespace Courseware\BlockTypes;

use Opis\JsonSchema\Schema;

/**
 * This class represents the content of a Courseware canvas block.
 *
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.0
 */
class Canvas extends BlockType
{
    public static function getType(): string
    {
        return 'canvas';
    }

    public static function getTitle(): string
    {
        return _('Leinwand');
    }

    public static function getDescription(): string
    {
        return _('Zeichnen und Schreiben auf einem Bild.');
    }

    public function initialPayload(): array
    {
        return [
            'tilte' => '',
            'image' => 'false',
            'file_id' => '',
            'upload_folder_id' => '',
            'show_usersdata' => 'off',
        ];
    }

    public static function getJsonSchema(): Schema
    {
        $schemaFile = __DIR__.'/Canvas.json';

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
        return ['interaction'];
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
