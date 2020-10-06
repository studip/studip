<?php

use JsonApi\Routes\News\ByUserIndex;

class UsersShowNewsTest extends \Codeception\Test\Unit
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

    //testszenario:
    public function testUsersShowNews()
    {
        $credentials = $this->tester->getCredentialsForTestAutor();
        $userId = $credentials['id'];
        $response = $this->getUserNews($credentials, $userId);
    }

    //helpers:
    private function getUserNews($credentials, $userId)
    {
        $app = $this->tester->createApp($credentials, 'get', '/users/{id}/news', ByUserIndex::class);

        return $this->tester->sendMockRequest(
                $app,
                $this->tester->createRequestBuilder($credentials)
                ->setUri('/users/'.$userId.'/news')
                ->fetch()
                ->getRequest()
        );
    }
}
