<?php


use JsonApi\Routes\ActivityStreamShow;

class ActivityStreamShowTest extends \Codeception\Test\Unit
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
    public function testShowEmptyStream()
    {
        $credentials = $this->tester->getCredentialsForTestAutor();

        $response = $this->getActivityStreamForUser($credentials);
        $this->tester->assertTrue($response->isSuccessfulDocument([200]));

        $document = $response->document();
        $this->tester->assertTrue($document->isResourceCollectionDocument());
        $this->tester->assertEmpty($document->primaryResources());
    }

    /*
    public function testShowNotEmptyStream()
    {
        $credentials = $this->tester->getCredentialsForTestAutor();
        $content = 'Eneios likes water';
        $this->createBlubberActivity($credentials, $content);

        $response = $this->getActivityStreamForUser($credentials);
        $this->tester->assertTrue($response->isSuccessfulDocument([200]));

        $document = $response->document();
        $this->tester->assertTrue($document->isResourceCollectionDocument());

        $resources = $document->primaryResources();
        $this->tester->assertNotEmpty($resources);

        $resource = current($resources);
        $this->assertContains($content, $resource->attribute('content'));
    }
    */

    // **** helper functions ****
    private function getActivityStreamForUser($credentials)
    {
        $app = $this->tester->createApp($credentials, 'get', '/users/{id}/activitystream', ActivityStreamShow::class);

        $requestBuilder = $this->tester->createRequestBuilder($credentials);
        $requestBuilder
            ->setUri('/users/'.$credentials['id'].'/activitystream')
            ->fetch();

        return $this->tester->sendMockRequest($app, $requestBuilder->getRequest());
    }

    /*
    private function createBlubberActivity($credentials, $content)
    {
        $posting = $this->createBlubberForUser($credentials, $content);
        \Studip\Activity\BlubberProvider::postActivity('ignored', $posting, true);
    }
    */
}
