<?php

require_once 'ForumTestHelper.php';

use JsonApi\Models\ForumCat;
use JsonApi\Routes\Forum\ForumCategoriesDelete;
use JsonApi\Errors\RecordNotFoundException;

class ForumCategoryDeleteTest extends \Codeception\Test\Unit
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

    public function testShouldDeleteEntry()
    {
        $credentials = $this->tester->getCredentialsForTestDozent();
        $cat = $this->createCategory($credentials);
        $app = $this->tester->createApp($credentials, 'delete', '/forum-categories/{id}', ForumCategoriesDelete::class);

        $requestBuilder = $this->tester->createRequestBuilder($credentials);
        $requestBuilder
            ->setUri('/forum-categories/'.$cat->id)
            ->delete();

        $response = $this->tester->sendMockRequest($app, $requestBuilder->getRequest());

        $this->tester->assertIsEmpty(ForumCat::find($cat->id));
    }

    public function testShouldNotDeleteEntry()
    {
        $this->tester->expectThrowable(RecordNotFoundException::class, function () {
            $credentials = $this->tester->getCredentialsForTestDozent();
            $cat = $this->createCategory($credentials);
            $entry = $this->createEntry($credentials, $cat->id);
            $app = $this->tester->createApp($credentials, 'delete', '/forum-categories/{id}', ForumCategoriesDelete::class);

            $requestBuilder = $this->tester->createRequestBuilder($credentials);
            $requestBuilder
                ->setUri('/forum-categories/badId')
                ->delete();

            $response = $this->tester->sendMockRequest($app, $requestBuilder->getRequest());
            $this->tester->assertIsEmpty(ForumCat::find($cat->id));
        });
    }
}
