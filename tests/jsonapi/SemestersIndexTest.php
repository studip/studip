<?php

use JsonApi\Routes\SemestersIndex;

class SemestersIndexTest extends \Codeception\Test\Unit
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

    public function testGetSemestersIndex()
    {
        $app = $this->tester->createApp(null, 'get', '/semesters', SemestersIndex::class);

        $requestBuilder = $this->tester->createRequestBuilder(null);
        $requestBuilder->setUri('/semesters')->fetch();

        $response = $this->tester->sendMockRequest($app, $requestBuilder->getRequest());
        $this->tester->assertTrue($response->isSuccessfulDocument([200]));

        $document = $response->document();
        $this->tester->assertTrue($document->isResourceCollectionDocument());

        $numberOfAllSemesters = count(\Semester::getAll());
        $this->tester->assertSame($numberOfAllSemesters, count($document->primaryResources()));
    }
}
