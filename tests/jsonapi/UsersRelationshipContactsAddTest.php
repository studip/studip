<?php


use JsonApi\Routes\Users\Rel\Contacts;
use JsonApi\Schemas\User as UserSchema;

class UsersRelationshipContactsAddTest extends \Codeception\Test\Unit
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
        $this->tester->assertCount(0, \User::find($credentials['id'])->contacts);
    }

    public function testShouldShowContactsAfterCreatingThem()
    {
        $credentials = $this->tester->getCredentialsForTestAutor();

        $response = $this->addContactToUser(
            $credentials,
            [
                $this->tester->getCredentialsForTestDozent()['id'],
                $this->tester->getCredentialsForTestDozent()['id'],
                $this->tester->getCredentialsForTestDozent()['id'],
                $this->tester->getCredentialsForTestDozent()['id'],
                $this->tester->getCredentialsForRoot()['id'],
            ]
        );
        $this->tester->assertSame(204, $response->getStatusCode());
        $this->tester->assertCount(2, \User::find($credentials['id'])->contacts);
    }

    // **** helper functions ****
    private function addContactToUser($credentials, array $contactIds)
    {
        return $this->tester->sendMockRequest(
            $this->tester->createApp($credentials, 'post', '/users/{id}/relationships/contacts', Contacts::class),
            $this->tester->createRequestBuilder($credentials)
            ->setUri('/users/'.$credentials['id'].'/relationships/contacts')
            ->setJsonApiBody($this->prepareValidBody($contactIds))
            ->create()
            ->getRequest()
        );
    }

    private function prepareValidBody(array $contactIds)
    {
        return [
            'data' => array_map(
                function ($contactId) {
                    return [
                        'type' => UserSchema::TYPE,
                        'id' => $contactId,
                    ];
                },
                $contactIds
            ),
        ];
    }
}
