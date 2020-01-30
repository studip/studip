<?php

use JsonApi\Routes\Schedule\SeminarCycleDatesShow;

class SeminarCycleDatesShowTest extends \Codeception\Test\Unit
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

    public function testGetSeminarCycleDates()
    {
        $credentials = $this->tester->getCredentialsForTestDozent();
        $course = \Course::findOneBySQL('status = 1');
        $cycle = $this->createSeminarCycleDate($credentials, $course);

        $app = $this->tester->createApp($credentials, 'get', '/seminar-cycle-dates/{id}', SeminarCycleDatesShow::class);

        $requestBuilder = $this->tester->createRequestBuilder($credentials);
        $requestBuilder->setUri('/seminar-cycle-dates/'.$cycle->id)->fetch();

        $response = $this->tester->sendMockRequest($app, $requestBuilder->getRequest());
        $this->tester->assertTrue($response->isSuccessfulDocument([200]));

        $document = $response->document();
        $this->tester->assertTrue($document->isSingleResourceDocument());

        $resource = $document->primaryResource();

        $this->tester->assertEquals($cycle->id, $resource->id());
    }

    private function createSeminarCycleDate($credentials, \Course $course)
    {
        // EVIL HACK
        $oldUser = $GLOBALS['user'];
        $oldAuth = $GLOBALS['auth'];

        $GLOBALS['user'] = new \Seminar_User(
            \User::find($credentials['id'])
        );
        $GLOBALS['auth'] = new \Seminar_Auth();
        $GLOBALS['auth']->auth = ['uid' => $credentials['id']];

        $cycle = \SeminarCycleDate::create(
            [
                'seminar_id' => $course->id,
                'weekday' => 0, // sunday
                'description' => 'Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.',
                'sws' => 1,
                'start_time' => date('H:i:00', strtotime('09:00')),
                'end_time' => date('H:i:00', strtotime('10:00')),

                'cycle' => 0,
                'week_offset' => 0,
                'end_offset' => null,
            ]
        );

        // EVIL HACK
        $GLOBALS['user'] = $oldUser;
        $GLOBALS['auth'] = $oldAuth;

        return $cycle;
    }
}
