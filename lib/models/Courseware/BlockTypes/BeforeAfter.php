<?php

namespace Courseware\BlockTypes;

use Opis\JsonSchema\Schema;

/**
 * This class represents the content of a Courseware before after block.
 *
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.0
 */
class BeforeAfter extends BlockType
{
    public static function getType(): string
    {
        return 'before-after';
    }

    public static function getTitle(): string
    {
        return _('Bildvergleich');
    }

    public static function getDescription(): string
    {
        return _('Vergleicht zwei Bilder mit einem Schieberegler.');
    }

    public function initialPayload(): array
    {
        return [
            'before_source' => 'studip',
            'before_file_id' => '',
            'before_web_url' => '',
            'after_source' => 'studip',
            'after_file_id' => '',
            'after_web_url' => '',
        ];
    }

    public static function getJsonSchema(): Schema
    {
        $schemaFile = __DIR__.'/BeforeAfter.json';

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
        if ($payload['before_file_id']) {
            if ($fileRef = \FileRef::find($payload['before_file_id'])) {
                $fileref['metadata'] = 'before';
                $files[] = $fileRef;
            }
        }

        if ($payload['after_file_id']) {
            if ($fileRef = \FileRef::find($payload['after_file_id'])) {
                $fileref['metadata'] = 'after';
                $files[] = $fileRef;
            }
        }

        return $files;
    }

    public function copyPayload(string $rangeId = ''): array
    {
        $payload = $this->getPayload();

        if ('' != $payload['before_file_id']) {
            $payload['before_file_id'] = $this->copyFileById($payload['before_file_id'], $rangeId);
        }

        if ('' != $payload['after_file_id']) {
            $payload['after_file_id'] = $this->copyFileById($payload['after_file_id'], $rangeId);
        }

        return $payload;
    }

    public static function getCategories(): array
    {
        return ['interaction', 'multimedia'];
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
