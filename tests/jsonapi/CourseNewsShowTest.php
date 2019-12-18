<?php

use JsonApi\Routes\News\ByCourseIndex;

class CourseNewsShowTest extends \Codeception\Test\Unit
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
    public function testCourseShowNews()
    {
        $courseId = 'a07535cf2f8a72df33c12ddfa4b53dde';
        $credentials = $this->tester->getCredentialsForTestAutor();
        $response = $this->getCourseNews($credentials, $courseId);
        $this->tester->assertTrue($response->isSuccessfulDocument([200]));
    }

    //helpers:
    private function getCourseNews($credentials, $courseId)
    {
        $app = $this->tester->createApp($credentials, 'get', '/courses/{id}/news', ByCourseIndex::class);

        return $this->tester->sendMockRequest(
                $app,
                $this->tester->createRequestBuilder($credentials)
                ->setUri('/courses/'.$courseId.'/news')
                ->fetch()
                ->getRequest()
        );
    }
}
