<?php


use JsonApi\Routes\Courses\CoursesIndex;

class CoursesIndexTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
        \DBManager::getInstance()->setConnection('studip', $this->getModule('\\Helper\\StudipDb')->dbh);
        //Initialize $SEM_TYPE and $SEM_CLASS arrays
        $GLOBALS['SEM_CLASS'] = SemClass::getClasses();
        $GLOBALS['SEM_TYPE'] = SemType::getTypes();
    }

    protected function _after()
    {
    }

    // tests

    public function testShouldIndexCourse()
    {
        $credentials = $this->tester->getCredentialsForRoot();
        $response = $this->getCourses($credentials);
        $this->tester->assertTrue($response->isSuccessfulDocument([200]));
        $this->tester->assertCount(2, $response->document()->primaryResources());
    }

    // **** helper functions ****
    private function getCourses($credentials)
    {
        $app = $this->tester->createApp($credentials, 'get', '/courses', CoursesIndex::class);

        return $this->tester->sendMockRequest(
            $app,
            $this->tester->createRequestBuilder($credentials)
            ->setUri('/courses')
            ->fetch()
            ->getRequest()
        );
    }
}
