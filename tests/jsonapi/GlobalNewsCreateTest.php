<?php

use JsonApi\Routes\News\StudipNewsCreate;

class GlobalNewsCreateTest extends \Codeception\Test\Unit
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
    public function testStudipCreateNews()
    {
        $title = 'Fakenews';
        $content = 'This is just fake news.';
        $date = time();
        $expire = $date + 1 * 7 * 24 * 60 * 60;
        $credentials = $this->tester->getCredentialsForRoot();
        $response = $this->createStudipNews($credentials, $title, $content, $date, $expire);
    }

    //helpers:
    private function createStudipNews($credentials, $title, $content, $date, $expire)
    {
        $app = $this->tester->createApp($credentials, 'post', '/news', StudipNewsCreate::class);

        return $this->tester->sendMockRequest(
                $app,
                $this->tester->createRequestBuilder($credentials)
                ->setUri('/news')
                ->fetch()
                ->getRequest()
        );
    }
}
