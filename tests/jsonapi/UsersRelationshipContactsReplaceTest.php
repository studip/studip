<?php

use JsonApi\Routes\Users\Rel\Contacts;
use JsonApi\Schemas\User as UserSchema;

class UsersRelationshipContactsReplaceTest extends \Codeception\Test\Unit
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
    public function testShouldReplaceContacts()
    {
        $credentials = $this->tester->getCredentialsForTestAutor();
        $credentials2 = $this->tester->getCredentialsForTestDozent();
        $credentials3 = $this->tester->getCredentialsForRoot();

        $this->assertNotEmpty(
            \Contact::create(
                [
                    'owner_id' => $credentials['id'],
                    'user_id' => $credentials2['id'],
                ]
            )
        );

        $response = $this->replaceContactsOfUser(
            $credentials,
            [$credentials3['id']]
        );

        $this->tester->assertSame(204, $response->getStatusCode());
        $this->tester->assertCount(1, \User::find($credentials['id'])->contacts);
    }

    // **** helper functions ****
    private function replaceContactsOfUser($credentials, array $contactIds)
    {
        return $this->tester->sendMockRequest(
            $this->tester->createApp($credentials, 'patch', '/users/{id}/relationships/contacts', Contacts::class),
            $this->tester->createRequestBuilder($credentials)
            ->setUri('/users/'.$credentials['id'].'/relationships/contacts')
            ->setJsonApiBody($this->prepareValidBody($contactIds))
            ->update()
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
