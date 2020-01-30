<?php

require_once 'ForumTestHelper.php';

use JsonApi\Routes\Forum\ForumCategoriesIndex;
use JsonApi\Errors\RecordNotFoundException;

class ForumCategoriesIndexTest extends \Codeception\Test\Unit
{
    use ForumTestHelper;

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

    // tests
    public function testShouldShowCategory()
    {
        $credentials = $this->tester->getCredentialsForTestAutor();
        $course_id = 'a07535cf2f8a72df33c12ddfa4b53dde';
        $cat = $this->createCategory($credentials);
        $app = $this->tester->createApp($credentials, 'get', '/course/{id}/forum-categories', ForumCategoriesIndex::class);

        $requestBuilder = $this->tester->createRequestBuilder($credentials);
        $requestBuilder
            ->setUri('/course/'.$course_id.'/forum-categories')
            ->fetch();

        $response = $this->tester->sendMockRequest($app, $requestBuilder->getRequest());

        $this->tester->assertTrue($response->isSuccessfulDocument([200]));
        $document = $response->document();
        $resourceObject = $document->primaryResource();
    }

    public function testShouldNotShowCategory()
    {
        $this->tester->expectThrowable(RecordNotFoundException::class, function () {
            $credentials = $this->tester->getCredentialsForTestDozent();
            $course_id = 'a07535cf2f8a72df33c12ddfa4b53dde';
            $cat = $this->createCategory($credentials);

            $app = $this->tester->createApp($credentials, 'get', '/course/{id}/forum-categories', ForumCategoriesIndex::class);

            $requestBuilder = $this->tester->createRequestBuilder($credentials);
            $requestBuilder
                ->setUri('/course/badID/forum-categories')
                ->fetch();

            $response = $this->tester->sendMockRequest($app, $requestBuilder->getRequest());

            $this->tester->assertTrue($response->isSuccessfulDocument([200]));
            $document = $response->document();
            $resourceObject = $document->primaryResource();
        });
    }
}
