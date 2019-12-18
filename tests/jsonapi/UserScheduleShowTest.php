<?php

use JsonApi\Routes\Schedule\UserScheduleShow;

class UserScheduleShowTest extends \Codeception\Test\Unit
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

    public function testGetUserSchedule()
    {
        $credentials = $this->tester->getCredentialsForTestAutor();

        $stmt = \DBManager::get()->prepare(
            "INSERT INTO schedule (start, end, day, title, content, color, user_id)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute(
            [
                1000, 1200, 1,
                'a title', 'some content',
                '#c0ffee', $credentials['id']
            ]
        );
        $scheduleId = \DBManager::get()->lastInsertId();

        $app = $this->tester->createApp($credentials, 'get', '/users/{id}/schedule', UserScheduleShow::class, 'get-schedule');

        $requestBuilder = $this->tester->createRequestBuilder($credentials);
        $requestBuilder->setUri('/users/'.$credentials['id'].'/schedule')->fetch();

        $response = $this->tester->sendMockRequest($app, $requestBuilder->getRequest());
        $this->tester->assertTrue($response->isSuccessfulDocument([200]));

        $document = $response->document();
        $this->tester->assertTrue($document->isResourceCollectionDocument());

        $ids = array_map(function ($property) { return $property->id(); }, $document->primaryResources());

        $this->tester->assertContains($scheduleId, $ids);
    }
}
