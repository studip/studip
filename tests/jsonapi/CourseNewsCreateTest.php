<?php


class CourseNewsCreateTest extends \Codeception\Test\Unit
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
    public function testCourseCreateNews()
    {
        $courseId = 'a07535cf2f8a72df33c12ddfa4b53dde';
        $title = 'Fakenews';
        $content = 'This is just fake news.';
        $date = time();
        $expire = $date + 1 * 7 * 24 * 60 * 60;
        $credentials = $this->tester->getCredentialsForRoot();
        $response = $this->createCourseNews($credentials, $courseId, $title, $content, $date, $expire);
    }

    //helpers:
    private function createCourseNews($credentials, $courseId, $title, $content, $date, $expire)
    {
        $app = $this->tester->createApp($credentials, 'post', '/courses/{id}/news', CourseNewsCreate::class);

        return $this->tester->sendMockRequest(
                $app,
                $this->tester->createRequestBuilder($credentials)
                ->setUri('/courses/'.$courseId.'/news')
                ->fetch()
                ->getRequest()
        );
    }
}
