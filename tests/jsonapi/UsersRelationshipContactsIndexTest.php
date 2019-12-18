<?php


use JsonApi\Routes\Users\Rel\Contacts;

class UsersRelationshipContactsIndexTest extends \Codeception\Test\Unit
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
    public function testShouldBeInitiallyEmpty()
    {
        $credentials = $this->tester->getCredentialsForTestAutor();

        $response = $this->getContactsRelationshipOfUser($credentials, $credentials['id']);
        $this->tester->assertTrue($response->isSuccessfulDocument([200]));
        $document = $response->document();
        $this->tester->assertTrue($document->isResourceCollectionDocument());

        $this->tester->assertCount(0, $document->primaryResources());
    }

    public function testShouldShowContactAfterCreatingOne()
    {
        $credentials = $this->tester->getCredentialsForTestAutor();

        $this->assertNotEmpty(
            \Contact::create(
                [
                    'owner_id' => $credentials['id'],
                    'user_id' => $this->tester->getCredentialsForTestDozent()['id'],
                ]
            )
        );

        $response = $this->getContactsRelationshipOfUser($credentials, $credentials['id']);
        $this->tester->assertTrue($response->isSuccessfulDocument([200]));
        $document = $response->document();
        $this->tester->assertCount(1, $document->primaryResources());
    }

    // **** helper functions ****
    private function getContactsRelationshipOfUser($credentials, $userId)
    {
        return $this->tester->sendMockRequest(
            $this->tester->createApp($credentials, 'get', '/users/{id}/relationships/contacts', Contacts::class),
            $this->tester->createRequestBuilder($credentials)
            ->setUri('/users/'.$userId.'/relationships/contacts')
            ->fetch()
            ->getRequest()
        );
    }
}
