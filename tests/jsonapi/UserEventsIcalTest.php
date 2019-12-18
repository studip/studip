<?php

use JsonApi\Routes\Events\UserEventsIcal;

class UserEventsIcalTest extends \Codeception\Test\Unit
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
    public function testIcalUserEvents()
    {
        $credentials = $this->tester->getCredentialsForTestAutor();

        $calendar = new \SingleCalendar($credentials['id']);
        $event = $calendar->getNewEvent();
        $event->setTitle('blypyp');

        $oldUser = $GLOBALS['user'];
        $GLOBALS['user'] = \User::find($credentials['id']);

        $calendar->storeEvent($event, [$credentials['id']]);

        $GLOBALS['user'] = $oldUser;

        $app = $this->tester->createApp($credentials, 'get', '/users/{id}/events.ics', UserEventsIcal::class);

        $requestBuilder = $this->tester->createRequestBuilder($credentials);
        $requestBuilder->setUri('/users/'.$credentials['id'].'/events.ics')->fetch();

        $response = $app($requestBuilder->getRequest(), new \Slim\Http\Response());

        $this->tester->assertEquals(200, $response->getStatusCode());
        $this->tester->assertContains('BEGIN:VEVENT', (string) $response->getBody());
        $this->tester->assertContains('SUMMARY:blypyp', (string) $response->getBody());
    }
}
