<?php


use JsonApi\Routes\Courses\CoursesByUserIndex;

class CoursesByUserTest extends \Codeception\Test\Unit
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
    public function testShouldShowCoursesByUser()
    {
        // test_autor ist bereits in einer Test-Veranstaltung
        $courseId = 'a07535cf2f8a72df33c12ddfa4b53dde';
        $credentials = $this->tester->getCredentialsForTestAutor();

        $response = $this->getCoursesByUser($credentials, $credentials['id']);
        $this->tester->assertTrue($response->isSuccessfulDocument([200]));

        $document = $response->document();
        $this->assertSame(2, $document->meta()['page']['total']);
        $this->assertTrue($document->isResourceCollectionDocument());

        $courses = $response->document()->primaryResources();
        $this->assertCount(2, $courses);

        $courseIds = array_map(function ($course) { return $course->id(); }, $courses);
        $this->assertContains($courseId, $courseIds);
    }

    // **** helper functions ****
    private function getCoursesByUser($credentials, $userId)
    {
        $app = $this->tester->createApp($credentials, 'get', '/users/{id}/courses', CoursesByUserIndex::class);

        return $this->tester->sendMockRequest(
            $app,
            $this->tester->createRequestBuilder($credentials)
            ->setUri('/users/'.$userId.'/courses')
            ->fetch()
            ->getRequest()
        );
    }
}
