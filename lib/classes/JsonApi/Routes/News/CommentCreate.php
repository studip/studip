<?php

namespace JsonApi\Routes\News;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\InternalServerError;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use JsonApi\Routes\ValidationTrait;

class CommentCreate extends JsonApiController
{
    use ValidationTrait;

    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$news = \StudipNews::find($args['id'])) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canCreateComment($user = $this->getUser($request), $news)) {
            throw new AuthorizationFailedException();
        }
        if (!$comment = $this->createCommentFromJSON($user, $news, $this->validate($request))) {
            throw new InternalServerError('Could not create comment.');
        }

        return $this->getCreatedResponse($comment);
    }

    protected function validateResourceDocument($json, $data)
    {
        if (empty(self::arrayGet($json, 'data.attributes.content'))) {
            return 'Comment should not be empty.';
        }
    }

    protected function createCommentFromJSON($user, \StudipNews $news, array $json)
    {
        $content = self::arrayGet($json, 'data.attributes.content');

        return $this->createComment($user, $news, $content);
    }

    protected function createComment($user, \StudipNews $news, $content)
    {
        $commentContent = \Studip\Markup::purifyHtml($content);
        $comment = new \StudipComment();
        $comment->user_id = $user->id;
        $comment->content = $commentContent;
        $comment->object_id = $news->id;

        $comment->store();

        return \StudipComment::find($comment->comment_id);
    }
}
