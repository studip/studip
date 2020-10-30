<?php

namespace JsonApi\Routes\Wiki;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\InternalServerError;
use JsonApi\JsonApiController;
use JsonApi\Routes\ValidationTrait;

require_once 'lib/wiki.inc.php';

/**
 * Create a news where the range is the user himself.
 */
class WikiUpdate extends JsonApiController
{
    use HelperTrait, ValidationTrait;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $json = $this->validate($request);
        $wikiPage = self::findWikiPage($args['id']);

        if (!Authority::canUpdateWiki($user = $this->getUser($request), $wikiPage)) {
            throw new AuthorizationFailedException();
        }

        if (!$page = $this->updateWikiFromJSON($user, $wikiPage, $json)) {
            throw new InternalServerError('Could not edit the wiki.');
        }

        return $this->getContentResponse($page);
    }

    protected function updateWikiFromJSON(\User $user, \WikiPage $wikiPage, $json)
    {
        $content = self::arrayGet($json, 'data.attributes.content');

        if (method_exists(\Studip\Markup::class, 'purifyHtml')) {
            $content = transformBeforeSave(\Studip\Markup::purifyHtml($content));
        }

        if ($wikiPage->body === $content) {
            return $wikiPage;
        }

        $checkTime = ceil((time() - $wikiPage->chdate) / 60);
        if ($checkTime <= 30 && $wikiPage->user_id == $user->id) {
            $wikiPage->chdate = time();
            $wikiPage->body = $content;
            $wikiPage->store();

            return $wikiPage;
        }

        // create a new version if the last change is at least 30
        // minutes ago or if the editing user differs
        return \WikiPage::create(
            array_merge(
                $wikiPage->toArray('keyword range_id'),
                [
                    'body' => $content,
                    'chdate' => time(),
                    'user_id' => $user->id,
                    'version' => $wikiPage->version + 1,
                ]
            )
        );
    }

    protected function validateResourceDocument($json, $data)
    {
        if (!self::arrayHas($json, 'data.attributes.content')) {
            return 'The content must not be empty.';
        }
    }
}
