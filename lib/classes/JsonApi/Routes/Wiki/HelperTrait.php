<?php

namespace JsonApi\Routes\Wiki;

use JsonApi\Errors\BadRequestException;
use JsonApi\Errors\RecordNotFoundException;

trait HelperTrait
{
    protected static function findWikiPage($wikiPageId)
    {
        if (!preg_match('/^([^_]+)_(.+)$/', $wikiPageId, $matches)) {
            throw new BadRequestException();
        }

        if (!$wikiPage = \WikiPage::findLatestPage($matches[1], $matches[2])) {
            throw new RecordNotFoundException();
        }

        return $wikiPage;
    }
}
