<?php

namespace Courseware\ContainerTypes;

use Opis\JsonSchema\Schema;

/**
 * This class represents the content of a Courseware accordion container stored in payload.
 *
 * @author  Marcus Eibrink-Lunzenauer <lunzenauer@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.0
 */
class AccordionContainer extends ContainerType
{
    public static function getType(): string
    {
        return 'accordion';
    }

    public static function getTitle(): string
    {
        return _('Accordion');
    }

    public static function getDescription(): string
    {
        return _('Mit diesem Abschnitt lassen sich Blöcke unter Überschriften gruppieren.');
    }

    public function initialPayload(): array
    {
        return [
            'colspan' => 'full',
            'sections' => [
                'name' => _('neues Fach'),
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
        $schemaFile = __DIR__.'/AccordionContainer.json';

        return Schema::fromJsonString(file_get_contents($schemaFile));
    }
}
