<?php

use JsonApi\Routes\CourseMemberships\ByUserIndex;

class CoursesMembershipsByUserTest extends \Codeception\Test\Unit
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

    public function testShouldShowMemberships()
    {
        $credentials = $this->tester->getCredentialsForTestDozent();

        $app = $this->tester->createApp($credentials, 'get', '/users/{id}/course-memberships', ByUserIndex::class);

        $response = $this->tester->sendMockRequest(
            $app,
            $this->tester->createRequestBuilder($credentials)
            ->setUri('/users/'.$credentials['id'].'/course-memberships')
            ->fetch()
            ->getRequest()
        );

        $this->tester->assertTrue($response->isSuccessfulDocument([200]));
        $document = $response->document();
        $this->tester->assertTrue($document->isResourceCollectionDocument());
        $resources = $document->primaryResources();
        $this->tester->assertCount(1, $resources);
    }
}
