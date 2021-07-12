<?php

namespace Courseware\ContainerTypes;

use Opis\JsonSchema\Schema;

/**
 * This class represents the content of a Courseware list container stored in payload.
 *
 * @author  Marcus Eibrink-Lunzenauer <lunzenauer@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.0
 */
class ListContainer extends ContainerType
{
    public static function getType(): string
    {
        return 'list';
    }

    public static function getTitle(): string
    {
        return _('Liste');
    }

    public static function getDescription(): string
    {
        return _('In diesem Abschnitt werden Blöcke untereinander dargestellt.');
    }

    public function initialPayload(): array
    {
        return [
            'colspan' => 'full',
            'sections' => [
                'name' => _('neue Liste'),
                'icon' => '',
                'blocks' => [],
            ],
        ];
    }

    public function addBlock($block): void
    {
        $payload = $this->getPayload();

        array_push($payload['sections'][0]['blocks'], $block->id);

        $this->setPayload($payload);
    }

    public static function getJsonSchema(): Schema
    {
        $schemaFile = __DIR__.'/ListContainer.json';

        return Schema::fromJsonString(file_get_contents($schemaFile));
    }
}
