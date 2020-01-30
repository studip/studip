<?php

require_once 'ForumTestHelper.php';

use JsonApi\Models\ForumEntry;
use JsonApi\Routes\Forum\ForumEntriesDelete;
use JsonApi\Errors\RecordNotFoundException;

class ForumEntriesDeleteTest extends \Codeception\Test\Unit
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
        $entry = $this->createEntry($credentials, $cat->id);
        $app = $this->tester->createApp($credentials, 'delete', '/forum-entries/{id}', ForumEntriesDelete::class);

        $requestBuilder = $this->tester->createRequestBuilder($credentials);
        $requestBuilder
            ->setUri('/forum-entries/'.$entry->id)
            ->delete()
            ->setJsonApiBody($entry_json);

        $response = $this->tester->sendMockRequest($app, $requestBuilder->getRequest());

        $this->tester->assertIsEmpty(ForumEntry::find($entry->id));
    }

    public function testShouldNotDeleteEntry()
    {
        $this->tester->expectThrowable(RecordNotFoundException::class, function () {
            $credentials = $this->tester->getCredentialsForTestDozent();
            $cat = $this->createCategory($credentials);
            $entry = $this->createEntry($credentials, $cat->id);
            $app = $this->tester->createApp($credentials, 'delete', '/forum-entries/{id}', ForumEntriesDelete::class);

            $requestBuilder = $this->tester->createRequestBuilder($credentials);
            $requestBuilder
                ->setUri('/forum-entries/badId')
                ->delete()
                ->setJsonApiBody($entry_json);

            $response = $this->tester->sendMockRequest($app, $requestBuilder->getRequest());
            $this->tester->assertIsEmpty(ForumEntry::find($entry->id));
        });
    }
}
