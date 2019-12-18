<?php

use JsonApi\Routes\Studip\PropertiesIndex;

class StudipPropertiesIndexTest extends \Codeception\Test\Unit
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

    public function testGetStudipPropertiesIndex()
    {
        $app = $this->tester->createApp(null, 'get', '/studip/properties', PropertiesIndex::class);

        $requestBuilder = $this->tester->createRequestBuilder(null);
        $requestBuilder->setUri('/studip/properties')->fetch();

        $response = $this->tester->sendMockRequest($app, $requestBuilder->getRequest());
        $this->tester->assertTrue($response->isSuccessfulDocument([200]));

        $document = $response->document();
        $this->tester->assertTrue($document->isResourceCollectionDocument());

        $ids = array_map(function ($property) { return $property->id(); }, $document->primaryResources());

        $this->tester->assertContains('studip-version', $ids);
    }
}
