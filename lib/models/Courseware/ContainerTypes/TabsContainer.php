<?php

namespace Courseware\ContainerTypes;

use Opis\JsonSchema\Schema;

/**
 * This class represents the content of a Courseware tabs container stored in payload.
 *
 * @author  Marcus Eibrink-Lunzenauer <lunzenauer@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.0
 */
class TabsContainer extends ContainerType
{
    public static function getType(): string
    {
        return 'tabs';
    }

    public static function getTitle(): string
    {
        return _('Tabs');
    }

    public static function getDescription(): string
    {
        return _('Dieser Abschnitt verfügt über eine horizontale Navigation, '
                . 'über die sich Gruppen von Blöcken erreichen lassen.');
    }

    public function initialPayload(): array
    {
        return [
            'colspan' => 'full',
            'sections' => [
                'name' => _('neuer Tab'),
                'icon' => '',
                'blocks' => [],
            ],
        ];
    }

    public function addBlock($block): void
    {
        $payload = $this->getPayload();

        array_push($payload['sections'][count($payload['sections']) - 1]['blocks'], $block->id);

        $this->setPayload($payload);
    }

    public static function getJsonSchema(): Schema
    {
        $schemaFile = __DIR__.'/TabsContainer.json';

        return Schema::fromJsonString(file_get_contents($schemaFile));
    }
}
