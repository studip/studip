<?php

use JsonApi\Routes\InstituteMemberships\InstituteMembershipsShow;

class InstitutesMembershipShowTest extends \Codeception\Test\Unit
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
        $membershipId = '205f3efb7997a0fc9755da2b535038da_2560f7c7674942a7dce8eeb238e15d93';

        $app = $this->tester->createApp($credentials, 'get', '/institute-memberships/{id}', InstituteMembershipsShow::class);

        $response = $this->tester->sendMockRequest(
            $app,
            $this->tester->createRequestBuilder($credentials)
            ->setUri('/institute-memberships/'.$membershipId)
            ->fetch()
            ->getRequest()
        );

        $this->tester->assertTrue($response->isSuccessfulDocument([200]));
        $document = $response->document();
        $this->tester->assertTrue($document->isSingleResourceDocument());
        $resource = $document->primaryResource();
        $this->tester->assertEquals($membershipId, $resource->id());
    }
}
