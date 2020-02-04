<?php

use JsonApi\Routes\Blubber\ThreadsShow;
use JsonApi\Routes\Blubber\CommentsByThreadIndex;
use JsonApi\Routes\Blubber\CommentsShow;

trait BlubberTestHelper
{
    private function createPublicBlubberThreadForUser(array $credentials, string $content = '')
    {
        $blubber = \BlubberThread::create(
            [
                'context_type' => 'public',
                'context_id' => $credentials['id'],
                'content' => $content,
                'user_id' => $credentials['id'],
                'external_contact' => 0,
                'visible_in_stream' => 1,
                'commentable' => 1,
            ]
        );

        return \BlubberThread::find($blubber->id);
    }

    private function createPrivateBlubberThreadForUser(array $credentials, array $mentions, string $content = '')
    {
        $blubber = \BlubberThread::create(
            [
                'context_type' => 'private',
                'context_id' => '',
                'content' => $content,
                'user_id' => $credentials['id'],
                'external_contact' => 0,
                'visible_in_stream' => 1,
                'commentable' => 1,
            ]
        );

        foreach ($mentions as $mention) {
            \BlubberMention::create(['thread_id' => $blubber->id, 'user_id' => $mention['id']]);
        }

        return \BlubberThread::find($blubber->id);
    }

    private function createCourseBlubberThreadForUser(array $credentials, string $courseId, string $content = '')
    {
        $blubber = \BlubberThread::create(
            [
                'context_type' => 'course',
                'context_id' => $courseId,
                'content' => $content,
                'user_id' => $credentials['id'],
                'external_contact' => 0,
                'visible_in_stream' => 1,
                'commentable' => 1,
            ]
        );

        return \BlubberThread::find($blubber->id);
    }

    private function createInstituteBlubberThreadForUser(array $credentials, \Institute $institute, string $content = '')
    {
        $blubber = \BlubberThread::create(
            [
                'context_type' => 'institute',
                'context_id' => $institute->id,
                'content' => $content,
                'user_id' => $credentials['id'],
                'external_contact' => 0,
                'visible_in_stream' => 1,
                'commentable' => 1,
            ]
        );

        return \BlubberThread::find($blubber->id);
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function createBlubberComment(array $credentials, \BlubberThread $thread, $content)
    {
        // Workaround old-style Stud.IP-API using $GLOBALS['user']
        $oldUser = $GLOBALS['user'];
        $GLOBALS['user'] = new \Seminar_User(\User::find($credentials['id']));

        $blubber = \BlubberComment::create(
            [
                'thread_id' => $thread->id,
                'content' => $content,
                'user_id' => $credentials['id'],
            ]
        );

        // Workaround old-style Stud.IP-API using $GLOBALS['user']
        $GLOBALS['user'] = $oldUser;


        return \BlubberComment::find($blubber->id);
    }

    private function fetchThread(array $credentials, \BlubberThread $resource)
    {
        return $this->tester->sendMockRequest(
            $this->tester->createApp($credentials, 'get', '/blubber-threads/{id}', ThreadsShow::class),
            $this->tester
            ->createRequestBuilder($credentials)
            ->setUri('/blubber-threads/'.$resource->id)
            ->fetch()
            ->getRequest()
        );
    }

    private function fetchComments(array $credentials, \BlubberThread $resource)
    {
        return $this->tester->sendMockRequest(
            $this->tester->createApp($credentials, 'get', '/blubber-threads/{id}/comments', CommentsByThreadIndex::class),
            $this->tester
            ->createRequestBuilder($credentials)
            ->setUri('/blubber-threads/'.$resource->id.'/comments')
            ->fetch()
            ->getRequest()
        );
    }

    private function fetchComment(array $credentials, \BlubberComment $resource)
    {
        return $this->tester->sendMockRequest(
            $this->tester->createApp($credentials, 'get', '/blubber-comments/{id}', CommentsShow::class),
            $this->tester
            ->createRequestBuilder($credentials)
            ->setUri('/blubber-comments/'.$resource->id)
            ->fetch()
            ->getRequest()
        );
    }

    private function upgradeAndFilterThreads(array $credentials, array $threads)
    {
        return array_filter(
            array_map(function ($thread) {
                return \BlubberThread::upgradeThread($thread);
            }, $threads),
            function ($thread) use ($user) {
                return $thread->isVisibleInStream() && $thread->isReadable($credentials['id']);
            }
        );
    }
}
