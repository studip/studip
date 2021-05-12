<?php

namespace Courseware\BlockTypes;

use Opis\JsonSchema\Schema;

/**
 * This class represents the content of a Courseware document block.
 *
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.0
 */
class Document extends BlockType
{
    public static function getType(): string
    {
        return 'document';
    }

    public static function getTitle(): string
    {
        return _('Dokument');
    }

    public static function getDescription(): string
    {
        return _('Zeigt ein Dokument aus dem Dateibereich an.');
    }

    public function initialPayload(): array
    {
        return [
            'title' => 'Dokument',
            'downloadable' => 'true',
            'file_id' => '',
            'doc_type' => 'pdf',
        ];
    }

    public static function getJsonSchema(): Schema
    {
        $schemaFile = __DIR__.'/Document.json';

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
        if (!$payload['file_id']) {
            return [];
        }

        $files = [];

        if ($fileRef = \FileRef::find($payload['file_id'])) {
            $files[] = $fileRef;
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
        return ['multimedia'];
    }

    public static function getContentTypes(): array
    {
        return ['text', 'document'];
    }

    public static function getFileTypes(): array
    {
        return ['document'];
    }
}
