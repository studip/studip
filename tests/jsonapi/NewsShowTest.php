<?php

require_once 'NewsTestHelper.php';
use JsonApi\Routes\News\NewsShow;
use JsonApi\Routes\News\ByCurrentUser;
use JsonApi\Routes\News\ByCourseIndex;
use JsonApi\Routes\News\GlobalNewsShow;

class NewsShowTest extends \Codeception\Test\Unit
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
    public function testShouldShowGlobalNews()
    {
        $credentials = $this->tester->getCredentialsForRoot();
        $title = 'A testing title';
        $content = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit';
        $range_id = 'studip';

        $news = $this->createNews($credentials, $title, $content, $range_id);
        $newsId = $news->id;
        $app = $this->tester->createApp($credentials, 'get', '/studip/news', GlobalNewsShow::class);
        
        $response = $this->tester->sendMockRequest(
            $app,
            $this->tester->createRequestBuilder($credentials)
            ->setUri('/studip/news')
            ->fetch()
            ->getRequest()
        );

        $this->tester->assertSame(200, $response->getStatusCode());
        $this->tester->assertTrue($response->isSuccessfulDocument([200]));
        $document = $response->document();
        $resourceObject = $document->primaryResource();
        $this->tester->assertNotNull($resourceObject->attribute('title'));
        $this->tester->assertNotNull($resourceObject->attribute('content'));
        $this->tester->assertNotNull($document->isSingleResourceDocument());
        $this->tester->assertSame($newsId, $document->primaryResource()->id());

        $this->tester->storeJsonMd('show_news', $response);
    }
    public function testShouldShowCourseNews()
    {
        $credentials = $this->tester->getCredentialsForTestDozent();
        $title = 'A testing title';
        $content = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit';
        $course_id = 'a07535cf2f8a72df33c12ddfa4b53dde';
        $news = $this->createNews($credentials, $title, $content, $course_id);
        $newsId = $news->id;
        $app = $this->tester->createApp($credentials, 'get', '/courses/{id}/news', ByCourseIndex::class);
        
        $response = $this->tester->sendMockRequest(
            $app,
            $this->tester->createRequestBuilder($credentials)
            ->setUri('/courses/'.$course_id.'/news')
            ->fetch()
            ->getRequest()
        );
        $this->tester->assertTrue($response->isSuccessfulDocument());
        $document = $response->document();
        $resourceObject = $document->primaryResource();
        $this->tester->assertNotNull($resourceObject->attribute('title'));
        $this->tester->assertNotNull($resourceObject->attribute('content'));
        
    }

    public function testShouldShowNewsByCurrentUser()
    {
        $credentials = $this->tester->getCredentialsForTestDozent();
        $title = 'A testing title for user';
        $content = 'fdsfsfdsfLorem ipsum dolor sit amet, consectetur adipisicing elit';
        $news = $this->createNews($credentials, $title, $content);

        $newsId = $news->id;
        $response = $this->getNewsByUser($credentials);
        $this->tester->assertSame(200, $response->getStatusCode());
        $this->tester->assertTrue($response->isSuccessfulDocument([200]));
        $document = $response->document();
        $this->tester->assertNotNull($document->primaryResource());
        $this->tester->assertSame($newsId, $document->primaryResource()->id());
    }

    public function testShouldNotShowNewsByCurrentUser()
    {
        $credentials = $this->tester->getCredentialsForTestDozent();
        $title = 'A testing title for user';
        $content = 'fdsfsfdsfLorem ipsum dolor sit amet, consectetur adipisicing elit';
        $this->createNews($credentials, $title, $content);

        $response = $this->getNoNewsByUser($credentials);
        $this->tester->assertSame(200, $response->getStatusCode());
        $this->tester->assertTrue($response->isSuccessfulDocument([200]));
        $document = $response->document();
        $this->tester->assertNull($document->primaryResource());
    }

    private function getNoNewsByUser($credentials)
    {
        $credentials = $this->tester->getCredentialsForTestAutor();

        $app = $this->tester->createApp($credentials, 'get', '/news', ByCurrentUser::class);

        return $this->tester->sendMockRequest(
                $app,
                $this->tester->createRequestBuilder($credentials)
                ->setUri('/news')
                ->fetch()
                ->getRequest()
        );
    }

    private function getNewsByUser($credentials)
    {
        $app = $this->tester->createApp($credentials, 'get', '/news', ByCurrentUser::class);

        return $this->tester->sendMockRequest(
                $app,
                $this->tester->createRequestBuilder($credentials)
                ->setUri('/news')
                ->fetch()
                ->getRequest()
        );
    }

    //helpers:
    private function getNews($credentials, $newsId)
    {
        $app = $this->tester->createApp($credentials, 'get', '/news/{id}', NewsShow::class);

        return $this->tester->sendMockRequest(
                $app,
                $this->tester->createRequestBuilder($credentials)
                ->setUri('/news/'.$newsId)
                ->fetch()
                ->getRequest()
        );
    }
}
