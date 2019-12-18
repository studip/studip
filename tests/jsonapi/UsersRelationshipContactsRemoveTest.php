<?php


use JsonApi\Routes\Users\Rel\Contacts;
use JsonApi\Schemas\User as UserSchema;

class UsersRelationshipContactsRemoveTest extends \Codeception\Test\Unit
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
    public function testShouldRemoveContacts()
    {
        $credentials = $this->tester->getCredentialsForTestAutor();
        $credentials2 = $this->tester->getCredentialsForTestDozent();
        $credentials3 = $this->tester->getCredentialsForRoot();

        \Contact::create(
            [
                'owner_id' => $credentials['id'],
                'user_id' => $credentials2['id'],
            ]
        );
        \Contact::create(
            [
                'owner_id' => $credentials['id'],
                'user_id' => $credentials3['id'],
            ]
        );

        $response = $this->removeContactsOfUser($credentials, [$credentials2['id']]);

        $this->tester->assertSame(204, $response->getStatusCode());
        $this->tester->assertCount(1, \User::find($credentials['id'])->contacts);
    }

    // **** helper functions ****
    private function removeContactsOfUser($credentials, array $contactIds)
    {
        return $this->tester->sendMockRequest(
            $this->tester->createApp($credentials, 'delete', '/users/{id}/relationships/contacts', Contacts::class),
            $this->tester->createRequestBuilder($credentials)
            ->setUri('/users/'.$credentials['id'].'/relationships/contacts')
            ->setJsonApiBody($this->prepareValidBody($contactIds))
            ->delete()
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
