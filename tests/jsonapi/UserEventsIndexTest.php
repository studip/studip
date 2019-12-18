<?php

use JsonApi\Routes\Events\UserEventsIndex;

class UserEventsIndexTest extends \Codeception\Test\Unit
{
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
    public function testIndexUserEvents()
    {
        $credentials = $this->tester->getCredentialsForTestAutor();

        $calendar = new \SingleCalendar($credentials['id']);
        $event = $calendar->getNewEvent();

        $oldUser = $GLOBALS['user'];
        $GLOBALS['user'] = \User::find($credentials['id']);

        $calendar->storeEvent($event, [$credentials['id']]);

        $GLOBALS['user'] = $oldUser;

        $app = $this->tester->createApp($credentials, 'get', '/users/{id}/events', UserEventsIndex::class);

        $requestBuilder = $this->tester->createRequestBuilder($credentials);
        $requestBuilder->setUri('/users/'.$credentials['id'].'/events')->fetch();

        $response = $this->tester->sendMockRequest($app, $requestBuilder->getRequest());

        $this->tester->assertTrue($response->isSuccessfulDocument([200]));
        $document = $response->document();
        $this->tester->assertTrue($document->isResourceCollectionDocument());
        $resources = $document->primaryResources();
        $this->tester->assertCount(1, $resources);
    }
}
