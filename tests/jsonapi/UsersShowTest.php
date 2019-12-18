<?php

use JsonApi\Routes\Users\UsersShow;

class UsersShowTest extends \Codeception\Test\Unit
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
    public function testShouldShowUser()
    {
        $credentials = $this->tester->getCredentialsForTestAutor();
        $dozent = $this->tester->getCredentialsForTestDozent();
        $response = $this->getUser($credentials, $dozent['id']);
        $this->tester->assertTrue($response->isSuccessfulDocument([200]));

        $this->tester->assertSame($dozent['id'], $response->document()->primaryResource()->id());

        $this->tester->storeJsonMd('get_own_user', $response);
    }

    // **** helper functions ****
    private function getUser($credentials, $userId)
    {
        $app = $this->tester->createApp($credentials, 'get', '/users/{id}', UsersShow::class);

        return $this->tester->sendMockRequest(
            $app,
            $this->tester->createRequestBuilder($credentials)
            ->setUri('/users/'.$userId)
            ->fetch()
            ->getRequest()
        );
    }
}
