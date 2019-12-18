<?php

require_once 'NewsTestHelper.php';
use JsonApi\Routes\News\NewsDelete;
use JsonApi\Routes\News\CommentsDelete;

class NewsDeleteTest extends \Codeception\Test\Unit
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

    public function testShouldDeleteNews()
    {
        $credentials = $this->tester->getCredentialsForRoot();
        $title = 'A testing title';
        $content = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit';
        $news = $this->createNews($credentials, $title, $content);

        $newsId = $news->id;

        $response = $this->deleteNews($credentials, $newsId);
        $this->tester->assertSame(204, $response->getStatusCode());
    }

    private function deleteNews(array $credentials, $newsId)
    {
        $app = $this->tester->createApp($credentials, 'delete', '/news/{id}', NewsDelete::class);

        return $this->tester->sendMockRequest(
            $app,
            $this->tester
            ->createRequestBuilder($credentials)
            ->setUri('/news/'.$newsId)
            ->delete()
            ->getRequest()
        );
    }
    public function testShouldCommentDelete()
    {
        
        $title = 'A course testing title';
        $credentials = $this->tester->getCredentialsForTestDozent();
        $content = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit';
        $news = $this->createNews($credentials, $title, $content);
        $changeContent = 'time for a change';
        $comment = $this->createComment($credentials, $news, $changeContent);

        $app = $this->tester->createApp($credentials, 'delete', '/comments/{id}', CommentsDelete::class);
        $requestBuilder = $this->tester->createRequestBuilder($credentials);
        $requestBuilder
            ->setUri('/comments/'.$comment->id)
            ->delete()
            ->setJsonApiBody($entry_json);
    
        $response = $this->tester->sendMockRequest($app, $requestBuilder->getRequest());
        $this->tester->assertIsEmpty(StudipComment::find($comment->id));
    }
}
