<?php

namespace Courseware\BlockTypes;

use Opis\JsonSchema\Schema;

/**
 * This class represents the content of a Courseware iFrame block.
 *
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.0
 */
class IFrame extends BlockType
{
    public static function getType(): string
    {
        return 'iframe';
    }

    public static function getTitle(): string
    {
        return _('IFrame');
    }

    public static function getDescription(): string
    {
        return _('Einbetten von einer Website oder Datei.');
    }

    public function initialPayload(): array
    {
        return [
            'url' => 'https://studip.de',
            'title' => 'Stud.IP',
            'height' => '400',
            'submit_user_id' => 'false',
            'submit_param' => 'uid',
            'salt' => md5(uniqid('', true)),
            'cc_info' => 'false',
            'cc_work' => '',
            'cc_author' => '',
            'cc_base' => '',
        ];
    }

    public static function getJsonSchema(): Schema
    {
        $schemaFile = __DIR__.'/IFrame.json';

        return Schema::fromJsonString(file_get_contents($schemaFile));
    }

    public static function getCategories(): array
    {
        return ['external'];
    }

    public static function getContentTypes(): array
    {
        return ['rich'];
    }

    public static function getFileTypes(): array
    {
        return [];
    }
}
