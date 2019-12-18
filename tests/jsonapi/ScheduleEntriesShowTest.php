<?php

use JsonApi\Routes\Schedule\ScheduleEntriesShow;

class ScheduleEntriesShowTest extends \Codeception\Test\Unit
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

    public function testShouldShowEntriesOfSchedule()
    {
        $credentials = $this->tester->getCredentialsForTestAutor();

        \CalendarScheduleModel::storeEntry(
            [
                'start' => 9,
                'end' => 10,
                'day' => 1,
                'title' => 'test title',
                'content' => 'test content',
                'user_id' => $credentials['id'],
                'color' => DEFAULT_COLOR_NEW
            ]
        );
        $scheduleEntryId = \DBManager::get()->lastInsertId();

        $app = $this->tester->createApp($credentials, 'get', '/schedule-entries/{id}', ScheduleEntriesShow::class);

        $response = $this->tester->sendMockRequest(
            $app,
            $this->tester->createRequestBuilder($credentials)
            ->setUri('/schedule-entries/'.$scheduleEntryId)
            ->fetch()
            ->getRequest()
        );

        $this->tester->assertTrue($response->isSuccessfulDocument([200]));
        $document = $response->document();
        $this->tester->assertTrue($document->isSingleResourceDocument());
        $resource = $document->primaryResource();
        $this->tester->assertNotNull($resource);
        $this->tester->assertEquals($scheduleEntryId, $resource->id());

    }
}
