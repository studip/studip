<?php

use JsonApi\Routes\SemestersShow;

class SemestersShowTest extends \Codeception\Test\Unit
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

    public function testGetSemestersShow()
    {
        $allSemesters = \Semester::getAll();
        $this->tester->assertTrue(0 < count($allSemesters));

        $semester = current($allSemesters);

        $app = $this->tester->createApp(null, 'get', '/semesters/{id}', SemestersShow::class);

        $requestBuilder = $this->tester->createRequestBuilder(null);
        $requestBuilder->setUri('/semesters/'.$semester->id)->fetch();

        $response = $this->tester->sendMockRequest($app, $requestBuilder->getRequest());
        $this->tester->assertTrue($response->isSuccessfulDocument([200]));

        $document = $response->document();
        $this->tester->assertTrue($document->isSingleResourceDocument());

        $this->tester->assertSame($semester->id, $document->primaryResource()->id());
    }
}
