<?php

require_once 'ForumTestHelper.php';

use JsonApi\Routes\Forum\ForumCategoriesUpdate;
use JsonApi\Errors\RecordNotFoundException;

class ForumCategoriesUpdateTest extends \Codeception\Test\Unit
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
    public function testShouldUpdateCategory()
    {
        $credentials = $this->tester->getCredentialsForTestAutor();
        $cat = $this->createCategory($credentials);
        $cat_document = $this->buildValidResourceCategoryUpdate();
        $app = $this->tester->createApp($credentials, 'PATCH', '/forum-categories/{id}', ForumCategoriesUpdate::class);

        $requestBuilder = $this->tester->createRequestBuilder($credentials);
        $requestBuilder
            ->setUri('/forum-categories/'.$cat->id)
            ->update()
            ->setJsonApiBody($cat_document);

        $response = $this->tester->sendMockRequest($app, $requestBuilder->getRequest());

        $this->tester->assertTrue($response->isSuccessfulDocument([200]));
        $document = $response->document();
        $resourceObject = $document->primaryResource();
        $this->tester->assertNotEquals($cat->entry_name, $resourceObject->attribute('title'));
    }

    public function testShouldNotUpdateCategory()
    {
        $this->tester->expectThrowable(RecordNotFoundException::class, function () {
            $credentials = $this->tester->getCredentialsForTestAutor();
            $cat = $this->createCategory($credentials);
            $cat_document = $this->buildValidResourceCategoryUpdate();
            $app = $this->tester->createApp($credentials, 'PATCH', '/forum-categories/{id}', ForumCategoriesUpdate::class);

            $requestBuilder = $this->tester->createRequestBuilder($credentials);
            $requestBuilder
                ->setUri('/forum-categories/badId')
                ->update()
                ->setJsonApiBody($cat_document);

            $response = $this->tester->sendMockRequest($app, $requestBuilder->getRequest());

            $this->tester->assertTrue($response->isSuccessfulDocument([200]));
            $document = $response->document();
            $resourceObject = $document->primaryResource();
            $this->tester->assertNotEquals($cat->entry_name, $resourceObject->attribute('title'));
        });
    }
}
