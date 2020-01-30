<?php

require_once 'ForumTestHelper.php';

use JsonApi\Routes\Forum\ForumEntriesUpdate;
use JsonApi\Errors\RecordNotFoundException;

class ForumEntriesUpdateTest extends \Codeception\Test\Unit
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

    public function testShouldUpdateEntry()
    {
        $credentials = $this->tester->getCredentialsForTestDozent();
        $cat = $this->createCategory($credentials);
        $entry = $this->createEntry($credentials, $cat->id);
        $entry_json = $this->buildValidResourceEntryUpdate();
        $app = $this->tester->createApp($credentials, 'PATCH', '/forum-entries/{id}', ForumEntriesUpdate::class);

        $requestBuilder = $this->tester->createRequestBuilder($credentials);
        $requestBuilder
            ->setUri('/forum-entries/'.$entry->id)
            ->update()
            ->setJsonApiBody($entry_json);

        $response = $this->tester->sendMockRequest($app, $requestBuilder->getRequest());
        $this->tester->assertTrue($response->isSuccessfulDocument([200]));
        $document = $response->document();
        $resourceObject = $document->primaryResource();
        $this->tester->assertNotEquals($entry->name, $resourceObject->attribute('title'));
    }

    public function testShouldNotUpdateEntry()
    {
        $this->tester->expectThrowable(RecordNotFoundException::class, function () {
            $credentials = $this->tester->getCredentialsForTestDozent();
            $cat = $this->createCategory($credentials);
            $entry = $this->createEntry($credentials, $cat->id);
            $entry_json = $this->buildValidResourceEntryUpdate();
            $app = $this->tester->createApp($credentials, 'PATCH', '/forum-entries/{id}', ForumEntriesUpdate::class);

            $requestBuilder = $this->tester->createRequestBuilder($credentials);
            $requestBuilder
                ->setUri('/forum-entries/badId')
                ->update()
                ->setJsonApiBody($entry_json);

            $response = $this->tester->sendMockRequest($app, $requestBuilder->getRequest());
            $this->tester->assertTrue($response->isSuccessfulDocument([200]));
            $document = $response->document();
            $resourceObject = $document->primaryResource();
            $this->tester->assertNotEquals($entry->name, $resourceObject->attribute('title'));
        });
    }
}
