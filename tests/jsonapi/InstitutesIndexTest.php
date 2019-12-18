<?php

use JsonApi\Routes\Institutes\InstitutesIndex;

class InstitutesIndexTest extends \Codeception\Test\Unit
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

    public function testGetInstitutesIndex()
    {
        $app = $this->tester->createApp(null, 'get', '/institutes', InstitutesIndex::class);

        $requestBuilder = $this->tester->createRequestBuilder(null);
        $requestBuilder->setUri('/institutes')->fetch();

        $response = $this->tester->sendMockRequest($app, $requestBuilder->getRequest());
        $this->tester->assertTrue($response->isSuccessfulDocument());

        $document = $response->document();
        $this->tester->assertTrue($document->isResourceCollectionDocument());

        $numberOfAllInstitutes = count(\Institute::getInstitutes());
        $this->tester->assertSame($numberOfAllInstitutes, count($document->primaryResources()));
    }
}
