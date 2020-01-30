<?php

require_once 'ForumTestHelper.php';

use JsonApi\Routes\Forum\ForumCategoryEntriesCreate;
use JsonApi\Routes\Forum\ForumEntryEntriesCreate;
use JsonApi\Errors\RecordNotFoundException;

class ForumEntriesCreateTest extends \Codeception\Test\Unit
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

    public function testShouldCreateEntryForCategory()
    {
        $credentials = $this->tester->getCredentialsForTestDozent();
        $cat = $this->createCategory($credentials);
        $content = 'some content to test';
        $title = 'entry-test-title';
        $entry_json = $this->buildValidResourceEntry($content, $title);
        $app = $this->tester->createApp($credentials, 'post', '/forum-categories/{id}/entries', ForumCategoryEntriesCreate::class);

        $requestBuilder = $this->tester->createRequestBuilder($credentials);
        $requestBuilder
            ->setUri('/forum-categories/'.$cat->id.'/entries')
            ->create()
            ->setJsonApiBody($entry_json);

        $response = $this->tester->sendMockRequest($app, $requestBuilder->getRequest());

        $this->tester->assertTrue($response->isSuccessfulDocument([201]));
        $document = $response->document();
        $resourceObject = $document->primaryResource();
        $this->tester->assertNotNull($resourceObject->attribute('title'));
        $this->tester->assertNotNull($resourceObject->attribute('content'));
    }

    public function testShouldNotCreateEntryForCategory()
    {
        $this->tester->expectThrowable(RecordNotFoundException::class, function () {
            $credentials = $this->tester->getCredentialsForTestDozent();
            $cat = $this->createCategory($credentials);
            $content = 'some content to test';
            $title = 'entry-test-title';
            $entry_json = $this->buildValidResourceEntry($content, $title);
            $app = $this->tester->createApp($credentials, 'post', '/forum-categories/{id}/entries', ForumCategoryEntriesCreate::class);

            $requestBuilder = $this->tester->createRequestBuilder($credentials);
            $requestBuilder
                ->setUri('/forum-categories/'.'badId'.'/entries')
                ->create()
                ->setJsonApiBody($entry_json);

            $response = $this->tester->sendMockRequest($app, $requestBuilder->getRequest());
            $this->tester->assertTrue($response->isSuccessfulDocument([201]));
            $document = $response->document();
            $resourceObject = $document->primaryResource();
            $this->tester->assertNotNull($resourceObject->attribute('title'));
            $this->tester->assertNotNull($resourceObject->attribute('content'));
        });
    }

    public function testShouldCreateEntryForEntry()
    {
        $credentials = $this->tester->getCredentialsForTestDozent();
        $cat = $this->createCategory($credentials);
        $entry = $this->createEntry($credentials, $cat->id);
        $content = 'some new content to test';
        $title = 'entry-test-title new';
        $entry_json = $this->buildValidResourceEntry($content, $title);
        $app = $this->tester->createApp($credentials, 'post', '/forum-entries/{id}/entries', ForumEntryEntriesCreate::class);
        $requestBuilder = $this->tester->createRequestBuilder($credentials);
        $requestBuilder
            ->setUri('/forum-entries/'.$entry->id.'/entries')
            ->create()
            ->setJsonApiBody($entry_json);

        $response = $this->tester->sendMockRequest($app, $requestBuilder->getRequest());

        $this->tester->assertTrue($response->isSuccessfulDocument([201]));
        $document = $response->document();
        $resourceObject = $document->primaryResource();
        $this->tester->assertNotNull($resourceObject->attribute('title'));
        $this->tester->assertNotNull($resourceObject->attribute('content'));
    }

    public function testShouldNotCreateEntryForEntry()
    {
        $this->tester->expectThrowable(RecordNotFoundException::class, function () {
            $credentials = $this->tester->getCredentialsForTestDozent();
            $cat = $this->createCategory($credentials);
            $entry = $this->createEntry($credentials, $cat->id);
            $content = 'some new content to test';
            $title = 'entry-test-title new';
            $entry_json = $this->buildValidResourceEntry($content, $title);
            $app = $this->tester->createApp($credentials, 'post', '/forum-entries/{id}/entries', ForumEntryEntriesCreate::class);

            $requestBuilder = $this->tester->createRequestBuilder($credentials);
            $requestBuilder
                ->setUri('/forum-entries/'.'badID'.'/entries')
                ->create()
                ->setJsonApiBody($entry_json);

            $response = $this->tester->sendMockRequest($app, $requestBuilder->getRequest());

            $this->tester->assertTrue($response->isSuccessfulDocument([201]));
            $document = $response->document();
            $resourceObject = $document->primaryResource();
            $this->tester->assertNotNull($resourceObject->attribute('title'));
            $this->tester->assertNotNull($resourceObject->attribute('content'));
        });
    }
}
