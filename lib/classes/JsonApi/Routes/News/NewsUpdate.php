<?php

namespace JsonApi\Routes\News;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use JsonApi\Routes\ValidationTrait;
use JsonApi\Schemas\StudipNews as NewsSchema;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Edits content of a news.
 */
class NewsUpdate extends JsonApiController
{
    use StudipNewsDatesHelper, ValidationTrait;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$news = \StudipNews::find($args['id'])) {
            throw new RecordNotFoundException();
        }

        $json = $this->validate($request, $news);

        if (!Authority::canEditNews($user = $this->getUser($request), $news)) {
            throw new AuthorizationFailedException();
        }

        if (!$news = $this->updateNewsFromJSON($user, $news, $json)) {
            throw new InternalServerError('Could not update news.');
        }

        return $this->getContentResponse($news);
    }

    protected function updateNewsFromJSON($user, $news, array $json)
    {
        $getField = function ($key, $default = null) use ($json) {
            return self::arrayGet($json, 'data.attributes.'.$key, $default);
        };

        if (self::arrayHas($json, 'data.attributes.title')) {
            $news->topic = $getField('title');
        }

        if (self::arrayHas($json, 'data.attributes.content')) {
            $content = $getField('content');
            if (method_exists(\Studip\Markup::class, 'purifyHtml')) {
                $content = \Studip\Markup::purifyHtml($content);
            }

            $news->body = $content;
        }

        if (self::arrayHas($json, 'data.attributes.comments-allowed')) {
            $commentsAllowed = $getField('comments-allowed');
            $news->allow_comments = (bool) $commentsAllowed;
        }

        list($news->date, $news->expire) = self::convertTimestampsToDateExpire(
            $getField('publication-start'),
            $getField('publication-end')
        );

        $news->user_id = $user->id;
        $news->author = get_fullname($user->id, 'full', false);

        $news->store();

        return $news;
    }

    protected function validateResourceDocument($json, \StudipNews $news)
    {
        if (NewsSchema::TYPE !== self::arrayGet($json, 'data.type')) {
            return 'Missing or wrong type.';
        }

        if (self::arrayHas($json, 'data.attributes.title')) {
            $title = self::arrayGet($json, 'data.attributes.title');
            if (!mb_strlen(trim($title))) {
                return 'The attribute `title` must not be empty.';
            }
        }

        if (self::arrayHas($json, 'data.attributes.content')) {
            $content = self::arrayGet($json, 'data.attributes.content');
            if (!mb_strlen(trim($content))) {
                return 'The attribute `content` must not be empty.';
            }
        }

        if (self::arrayHas($json, 'data.attributes.comments-allowed')) {
            $comments = self::arrayGet($json, 'data.attributes.comments-allowed');
            if (!is_bool($comments)) {
                return 'The attribute `comments` must be of type boolean.';
            }
        }

        if ($error = $this->checkNewsDates($json)) {
            return $error;
        }
    }
}
