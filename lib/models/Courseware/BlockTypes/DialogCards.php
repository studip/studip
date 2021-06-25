<?php

namespace Courseware\BlockTypes;

use Opis\JsonSchema\Schema;

/**
 * This class represents the content of a Courseware dialog cards block.
 *
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.0
 */
class DialogCards extends BlockType
{
    public static function getType(): string
    {
        return 'dialog-cards';
    }

    public static function getTitle(): string
    {
        return _('Dialogkarten');
    }

    public static function getDescription(): string
    {
        return _('Karten zum Umdrehen, auf beiden Seiten lässt sich ein Bild und Text darstellen.');
    }

    public function initialPayload(): array
    {
        return [
            'cards' => [['index' => 0, 'front_file_id' => '', 'front_text' => '', 'back_file_id' => '', 'back_text' => '']],
        ];
    }

    public function getPayload()
    {
        $payload = $this->decodePayloadString($this->block['payload']);

        foreach ($payload['cards'] as &$card) {
            if ($card['front_file_id']) {
                $card['front_file'] = $this->getFileById($card['front_file_id']);
            } else {
                $card['front_file'] = [];
            }
            if ($card['back_file_id']) {
                $card['back_file'] = $this->getFileById($card['back_file_id']);
            } else {
                $card['back_file'] = [];
            }
        }

        return $payload;
    }

    public function copyPayload(string $rangeId = ''): array
    {
        $payload = $this->getPayload();
        foreach ($payload['cards'] as &$card) {
            if ('' != $card['front_file_id']) {
                $card['front_file_id'] = $this->copyFileById($card['front_file_id'], $rangeId);
            }

            if ('' != $card['back_file_id']) {
                $card['back_file_id'] = $this->copyFileById($card['back_file_id'], $rangeId);
            }
        }

        return $payload;
    }

    public static function getJsonSchema(): Schema
    {
        $schemaFile = __DIR__.'/DialogCards.json';

        return Schema::fromJsonString(file_get_contents($schemaFile));
    }

    public static function getCategories(): array
    {
        return ['interaction'];
    }

    public static function getContentTypes(): array
    {
        return ['text', 'image'];
    }

    public static function getFileTypes(): array
    {
        return ['image'];
    }
}
