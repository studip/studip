<?php

require_once 'public/plugins_packages/core/Forum/models/ForumEntry.php';
require_once 'ForumTestHelper.php';

use JsonApi\Models\ForumEntry as ForumEntryModel;
use JsonApi\Routes\Forum\ForumEntriesShow;
use JsonApi\Routes\Forum\ForumCategoryEntriesIndex;
use JsonApi\Routes\Forum\ForumEntryEntriesIndex;
use JsonApi\Errors\RecordNotFoundException;

class ForumEntriesShowTest extends \Codeception\Test\Unit
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

    // Tests
    public function testShouldShowEntry()
    {
        $credentials = $this->tester->getCredentialsForRoot();
        $course = \Course::find('a07535cf2f8a72df33c12ddfa4b53dde');

        $this->tester->assertSame(0, ForumEntryModel::countBySql('1'));
        \ForumEntry::checkRootEntry($course->id);
        $entries = ForumEntryModel::findBySql(
            'seminar_id = ? ORDER BY depth DESC',
            [$course->id]
        );
        $this->tester->assertCount(2, $entries);
        $entry = current($entries);

        $app = $this->tester->createApp(
            $credentials,
            'get',
            '/forum-entries/{id}',
            ForumEntriesShow::class
        );

        $requestBuilder = $this->tester->createRequestBuilder($credentials);
        $requestBuilder
            ->setUri('/forum-entries/'.$entry->id)
            ->fetch();

        $response = $this->tester->sendMockRequest($app, $requestBuilder->getRequest());

        $this->tester->assertTrue($response->isSuccessfulDocument([200]));
    }

    public function testShouldNotShowEntry()
    {
        $this->tester->expectThrowable(RecordNotFoundException::class, function () {
            $credentials = $this->tester->getCredentialsForRoot();
            $app = $this->tester->createApp($credentials, 'get', '/forum-entries/{id}', ForumEntriesShow::class);

            $requestBuilder = $this->tester->createRequestBuilder($credentials);
            $requestBuilder
                ->setUri('/forum-entries/'.'badEntry')
                ->fetch();

            $response = $this->tester->sendMockRequest($app, $requestBuilder->getRequest());

            $this->tester->assertTrue($response->isSuccessfulDocument([200]));
        });
    }

    public function testShouldNotShowEntriesForCategory()
    {
        $this->tester->expectThrowable(RecordNotFoundException::class, function () {
            $credentials = $this->tester->getCredentialsForRoot();
            $cat = $this->createCategory($credentials);
            $this->createEntry($credentials, $cat->id);
            $this->createEntry($credentials, $cat->id);
            $this->createEntry($credentials, $cat->id);

            $app = $this->tester->createApp($credentials, 'get', '/forum-categories/{id}/entries', ForumCategoryEntriesIndex::class);

            $requestBuilder = $this->tester->createRequestBuilder($credentials);
            $requestBuilder
                ->setUri('/forum-categories/badID/entries')
                ->fetch();

            $response = $this->tester->sendMockRequest($app, $requestBuilder->getRequest());
            $document = $response->document();
            $resourceObject = $document->primaryResource();
            $this->tester->assertNotNull($resourceObject);
        });
    }

    public function testShouldShowEntriesForCategory()
    {
        $credentials = $this->tester->getCredentialsForRoot();
        $cat = $this->createCategory($credentials);
        $this->createEntry($credentials, $cat->id);
        $this->createEntry($credentials, $cat->id);
        $this->createEntry($credentials, $cat->id);

        $app = $this->tester->createApp($credentials, 'get', '/forum-categories/{id}/entries', ForumCategoryEntriesIndex::class);

        $requestBuilder = $this->tester->createRequestBuilder($credentials);
        $requestBuilder
            ->setUri('/forum-categories/'.$cat->id.'/entries')
            ->fetch();

        $response = $this->tester->sendMockRequest($app, $requestBuilder->getRequest());
        $document = $response->document();
        $resourceObject = $document->primaryResource();
        $this->tester->assertNotNull($resourceObject);
    }

    public function testShouldShowEntriesForEntry()
    {
        $credentials = $this->tester->getCredentialsForRoot();
        $cat = $this->createCategory($credentials);
        $target_entry = $this->createEntry($credentials, $cat->id);
        $this->createEntry($credentials, $target_entry->id);
        $this->createEntry($credentials, $target_entry->id);
        $this->createEntry($credentials, $target_entry->id);
        $app = $this->tester->createApp($credentials, 'get', '/forum-entries/{id}/entries', ForumEntryEntriesIndex::class);

        $requestBuilder = $this->tester->createRequestBuilder($credentials);
        $requestBuilder
            ->setUri('/forum-entries/'.$target_entry->topic_id.'/entries')
            ->fetch();

        $response = $this->tester->sendMockRequest($app, $requestBuilder->getRequest());
        $document = $response->document();
        $resourceObject = $document->primaryResource();
        $this->tester->assertNotNull($resourceObject);
    }

    public function testShouldNotShowEntriesForEntry()
    {
        $this->tester->expectThrowable(RecordNotFoundException::class, function () {
            $credentials = $this->tester->getCredentialsForRoot();
            $cat = $this->createCategory($credentials);
            $targetEntry = $this->createEntry($credentials, $cat->id);
            $this->createEntry($credentials, $targetEntry->id);
            $this->createEntry($credentials, $targetEntry->id);
            $this->createEntry($credentials, $targetEntry->id);
            $app = $this->tester->createApp($credentials, 'get', '/forum-entries/{id}/entries', ForumEntryEntriesIndex::class);

            $requestBuilder = $this->tester->createRequestBuilder($credentials);
            $requestBuilder
                ->setUri('/forum-entries/badTopic/entries')
                ->fetch();

            $response = $this->tester->sendMockRequest($app, $requestBuilder->getRequest());
            $document = $response->document();
            $resourceObject = $document->primaryResource();
            $this->tester->assertNotNull($resourceObject);
        });
    }
}
