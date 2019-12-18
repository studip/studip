<?php

use JsonApi\Routes\DiscoveryIndex;

class DiscoveryIndexTest extends \Codeception\Test\Unit
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
    public function testIndexRoutes()
    {
        $credentials = $this->tester->getCredentialsForTestAutor();

        $app = $this->tester->createApp($credentials, 'get', '/discovery', DiscoveryIndex::class);
        $app->get('/dummy-1', DiscoveryIndex::class);
        $app->get('/dummy-2', DiscoveryIndex::class);

        $requestBuilder = $this->tester->createRequestBuilder($credentials);
        $requestBuilder->setUri('/discovery')->fetch();

        $response = $this->tester->sendMockRequest($app, $requestBuilder->getRequest());

        $this->tester->assertTrue($response->isSuccessfulDocument([200]));
        $document = $response->document();
        $this->tester->assertTrue($document->isResourceCollectionDocument());
        $resources = $document->primaryResources();
        $this->tester->assertCount(3, $resources);
    }
}
