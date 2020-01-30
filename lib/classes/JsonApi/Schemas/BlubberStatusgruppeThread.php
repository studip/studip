<?php

namespace JsonApi\Schemas;

use JsonApi\Errors\InternalServerError;
use Neomerx\JsonApi\Document\Link;

class BlubberStatusgruppeThread extends BlubberThread
{
    const REL_STATUSGRUPPE = 'group';

    /**
     * In dieser Methode können Relationships zu anderen Objekten
     * spezifiziert werden.
     * {@inheritdoc}
     */
    public function getRelationships($resource, $isPrimary, array $includeList)
    {
        $relationships = parent::getRelationships($resource, $isPrimary, $includeList);

        $relationships[self::REL_STATUSGRUPPE] = [
            self::DATA => \Statusgruppen::build(
                [
                    'statusgruppe_id' => $resource['metadata']['statusgruppe_id']
                ],
                false
            )
        ];

        return $relationships;
    }
}
