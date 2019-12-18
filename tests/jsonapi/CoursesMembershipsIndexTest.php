<?php

use JsonApi\Routes\Courses\CoursesMembershipsIndex;

class CoursesMembershipsIndexTest extends \Codeception\Test\Unit
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
    public function testIndexMemberships()
    {
        $credentials = $this->tester->getCredentialsForTestAutor();
        $courseId = 'a07535cf2f8a72df33c12ddfa4b53dde';

        $course = \Course::find($courseId);

        $app = $this->tester->createApp($credentials, 'get', '/courses/{id}/memberships', CoursesMembershipsIndex::class);

        $requestBuilder = $this->tester->createRequestBuilder($credentials);
        $requestBuilder->setUri('/courses/'.$courseId.'/memberships')->fetch();

        $response = $this->tester->sendMockRequest($app, $requestBuilder->getRequest());

        $this->tester->assertTrue($response->isSuccessfulDocument([200]));
        $document = $response->document();
        $this->tester->assertTrue($document->isResourceCollectionDocument());
        $resources = $document->primaryResources();
        $this->tester->assertCount(count($course->members), $resources);
    }
}
