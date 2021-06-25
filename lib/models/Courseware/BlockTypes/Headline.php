<?php

namespace Courseware\BlockTypes;

use Opis\JsonSchema\Schema;
use Opis\JsonSchema\Validator;

/**
 * This class represents the content of a Courseware headline block.
 *
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.0
 */
class Headline extends BlockType
{
    public static function getType(): string
    {
        return 'headline';
    }

    public static function getTitle(): string
    {
        return _('Blickfang');
    }

    public static function getDescription(): string
    {
        return _('Erzeugt einen typografisch ansprechenden Text.');
    }

    public function initialPayload(): array
    {
        return [
            'title' => 'Courseware',
            'subtitle' => '',
            'style' => 'bigicon_before',
            'height' => 'half',
            'background_color' => '#28497c',
            'background_image_id' => '',
            'background_type' => 'color',
            'text_color' => '#ffffff',
            'icon' => 'learnmodule',
            'icon_color' => 'white',
        ];
    }

    public static function getJsonSchema(): Schema
    {
        $schemaFile = __DIR__.'/Headline.json';

        return Schema::fromJsonString(file_get_contents($schemaFile));
    }

    public function getPayload()
    {
        $payload = $this->decodePayloadString($this->block['payload']);

        if ('' != $payload['background_image_id']) {
            $payload['background_image'] = $this->getFileById($payload['background_image_id']);
        } else {
            $payload['background_image'] = [];
        }

        return $payload;
    }

    public function copyPayload(string $rangeId = ''): array
    {
        $payload = $this->getPayload();

        if ('' != $payload['background_image_id']) {
            $payload['background_image_id'] = $this->copyFileById($payload['background_image_id'], $rangeId);
            $payload['background_image'] = '';
        }

        return $payload;
    }

    public function validatePayload($payload): bool
    {
        unset($payload->background_image);
        $schema = static::getJsonSchema();
        $validator = new Validator();
        $result = $validator->schemaValidation($payload, $schema);

        return $result->isValid();
    }

    public static function getCategories(): array
    {
        return ['layout'];
    }

    public static function getContentTypes(): array
    {
        return ['text'];
    }

    public static function getFileTypes(): array
    {
        return [];
    }
}
