<?php

use JsonApi\Routes\Institutes\InstitutesShow;

class InstitutesShowTest extends \Codeception\Test\Unit
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

    public function testGetInstitutesShow()
    {
        $allInstitutes = \Institute::getInstitutes();
        $this->tester->assertTrue(0 < count($allInstitutes));

        $institute = current($allInstitutes);

        $app = $this->tester->createApp(null, 'get', '/institutes/{id}', InstitutesShow::class);

        $requestBuilder = $this->tester->createRequestBuilder(null);
        $requestBuilder->setUri('/institutes/'.$institute['Institut_id'])->fetch();

        $response = $this->tester->sendMockRequest($app, $requestBuilder->getRequest());
        $this->tester->assertTrue($response->isSuccessfulDocument());

        $document = $response->document();
        $this->tester->assertTrue($document->isSingleResourceDocument());

        $this->tester->assertSame($institute['Institut_id'], $document->primaryResource()->id());
    }
}
