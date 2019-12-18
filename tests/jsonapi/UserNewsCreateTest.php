<?php

use JsonApi\Routes\News\UserNewsCreate;

class UserNewsCreateTest extends \Codeception\Test\Unit
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
    public function testUsersCreateNews()
    {
        $title = 'Fakenews';
        $content = 'This is just fake news.';
        $date = time();
        $expire = $date + 1 * 7 * 24 * 60 * 60;
        $credentials = $this->tester->getCredentialsForRoot();
        $userId = $credentials['id'];
        $response = $this->createTestNews($credentials, $userId, $title, $content, $date, $expire);
    }

    //helpers:
    private function createTestNews($credentials, $userId, $title, $content, $date, $expire)
    {
        $app = $this->tester->createApp($credentials, 'post', '/users/{id}/news', UserNewsCreate::class);

        return $this->tester->sendMockRequest(
                $app,
                $this->tester->createRequestBuilder($credentials)
                ->setUri('/users/'.$userId.'/news')
                ->fetch()
                ->getRequest()
        );
    }
}
