<?php

namespace JsonApi\Routes\Wiki;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\ConflictError;
use JsonApi\Errors\InternalServerError;
use JsonApi\JsonApiController;
use JsonApi\Routes\ValidationTrait;
use JsonApi\Schemas\WikiPage;

require_once 'lib/wiki.inc.php';

/**
 * Create a news where the range is the user himself.
 */
class WikiCreate extends JsonApiController
{
    use ValidationTrait;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $json = $this->validate($request);

        // TODO: has to be Course or Institute
        if (!$range = \Course::find($args['id'])) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canCreateWiki($user = $this->getUser($request), $range)) {
            throw new AuthorizationFailedException();
        }

        $keyword = self::arrayGet($json, 'data.attributes.keyword');

        if (\WikiPage::findLatestPage($range->id, $keyword)) {
            throw new ConflictError('Wiki page already exists.');
        }

        if (!$wiki = $this->createWikiFromJSON($user, $range, $json)) {
            throw new InternalServerError('Could not create the wiki page.');
        }

        return $this->getCreatedResponse($wiki);
    }

    protected function createWikiFromJSON(\User $user, $range, $json)
    {
        $keyword = self::arrayGet($json, 'data.attributes.keyword');
        $content = self::arrayGet($json, 'data.attributes.content');

        if (method_exists(\Studip\Markup::class, 'purifyHtml')) {
            $content = transformBeforeSave(\Studip\Markup::purifyHtml($content));
        }

        $wiki = new \WikiPage();
        $wiki->keyword = $keyword;
        $wiki->body = $content;
        $wiki->version = 1;
        $wiki->chdate = time();
        $wiki->user_id = $user->id;
        $wiki->range_id = $range->id;
        $wiki->store();

        return $wiki;
    }

    protected function validateResourceDocument($json, $data)
    {
        $keyword = self::arrayGet($json, 'data.attributes.keyword', '');
        if (empty($keyword)) {
            return 'Wikis must have a title (keyword)';
        }

        if (!preg_match(WikiPage::REGEXP_KEYWORD, $keyword)) {
            return 'Malformed wiki keyword.';
        }
    }
}
