<?php

require_once 'ForumTestHelper.php';

use JsonApi\Routes\Forum\ForumCategoriesShow;
use JsonApi\Errors\RecordNotFoundException;

class ForumCategoriesShowTest extends \Codeception\Test\Unit
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
    public function testShouldShowCategories()
    {
        $credentials = $this->tester->getCredentialsForTestAutor();
        $cat = $this->createCategory($credentials);
        $app = $this->tester->createApp($credentials, 'get', '/forum-categories/{id}', ForumCategoriesShow::class);

        $requestBuilder = $this->tester->createRequestBuilder($credentials);
        $requestBuilder
            ->setUri('/forum-categories/'.$cat->category_id)
            ->fetch();

        $response = $this->tester->sendMockRequest($app, $requestBuilder->getRequest());

        $this->tester->assertTrue($response->isSuccessfulDocument([200]));
        $document = $response->document();
        $resourceObject = $document->primaryResource();

        $this->tester->assertSame($cat->entry_name, $resourceObject->attribute('title'));
        $this->tester->assertSame((int) $cat->pos, $resourceObject->attribute('position'));
    }

    public function testShouldNotShowCategories()
    {
        $this->tester->expectThrowable(RecordNotFoundException::class, function () {
            $credentials = $this->tester->getCredentialsForTestDozent();

            $app = $this->tester->createApp($credentials, 'get', '/forum-categories/{id}', ForumCategoriesShow::class);

            $requestBuilder = $this->tester->createRequestBuilder($credentials);
            $requestBuilder
                ->setUri('/forum-categories/'.'badId')
                ->fetch();

            $response = $this->tester->sendMockRequest($app, $requestBuilder->getRequest());

            $this->tester->assertTrue($response->isSuccessfulDocument([200]));
            $document = $response->document();
            $resourceObject = $document->primaryResource();

            $this->tester->assertSame($cat->entry_name, $resourceObject->attribute('title'));
            $this->tester->assertSame((int) $cat->pos, $resourceObject->attribute('position'));
        });
    }
}
