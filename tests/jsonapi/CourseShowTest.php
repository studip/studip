<?php


use JsonApi\Routes\Courses\CoursesShow;

class CourseShowTest extends \Codeception\Test\Unit
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

    public function testShouldShowCourse()
    {
        $courseId = 'a07535cf2f8a72df33c12ddfa4b53dde';
        $credentials = $this->tester->getCredentialsForTestAutor();
        $response = $this->getCourse($credentials, $courseId);

        $this->tester->assertSame($courseId, $response->document()->primaryResource()->id());
    }

    // **** helper functions ****
    private function getCourse($credentials, $courseId)
    {
        $app = $this->tester->createApp($credentials, 'get', '/courses/{id}', CoursesShow::class);

        return $this->tester->sendMockRequest(
            $app,
            $this->tester->createRequestBuilder($credentials)
            ->setUri('/courses/'.$courseId)
            ->fetch()
            ->getRequest()
        );
    }
}
