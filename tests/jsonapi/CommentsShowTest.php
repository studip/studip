<?php

require_once 'NewsTestHelper.php';
use JsonApi\Routes\News\CommentsIndex;
use JsonApi\Routes\News\CommentsShow;

class CommentsShowTest extends \Codeception\Test\Unit
{
    use NewsTestHelper;

    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
        \DBManager::getInstance()->setConnection('studip', $this->getModule('\\Helper\\StudipDb')->dbh);
    }

    protected function _after()
    {
    }

    //testszenario:
    public function testShowAllComments()
    {
        $credentials = $this->tester->getCredentialsForRoot();
        $title = 'A testing title';
        $content = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit';
        $news = $this->createNews($credentials, $title, $content);

        $newsId = $news->id;

        $response = $this->getComments($credentials, $newsId);
    }

    public function testShowOneComment()
    {
        $credentials = $this->tester->getCredentialsForRoot();
        $title = 'A testing title';
        $content = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit';
        $news = $this->createNews($credentials, $title, $content);

        $comment = $this->createComment($credentials, $news, $content);

        $response = $this->getComment($credentials, $comment);
        $this->tester->assertSame(200, $response->getStatusCode());
        $this->tester->assertTrue($response->isSuccessfulDocument([200]));
        $document = $response->document();
    }

    //helpers:
    private function getComments($credentials, $newsId)
    {
        $app = $this->tester->createApp($credentials, 'get', '/news/{id}/comments', CommentsIndex::class);

        return $this->tester->sendMockRequest(
                $app,
                $this->tester->createRequestBuilder($credentials)
                ->setUri('/news/'.$newsId.'/comments')
                ->fetch()
                ->getRequest()
        );
    }

    private function getComment($credentials, $comment)
    {
        $app = $this->tester->createApp($credentials, 'get', '/comments/{id}', CommentsShow::class);

        return $this->tester->sendMockRequest(
                $app,
                $this->tester->createRequestBuilder($credentials)
                ->setUri('/comments/'.$comment->id)
                ->fetch()
                ->getRequest()
        );
    }
}
