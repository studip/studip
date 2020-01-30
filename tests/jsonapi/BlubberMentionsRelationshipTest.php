<?php

use JsonApi\Routes\Blubber\Rel\Mentions;
use JsonApi\Schemas\User as UsersSchema;

require_once 'BlubberTestHelper.php';

class BlubberMentionsRelationshipTest extends \Codeception\Test\Unit
{
    use BlubberTestHelper;

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
    public function testFetchRelationship()
    {
        $credentials = $this->tester->getCredentialsForTestAutor();

        $thread = $this->createPrivateBlubberThreadForUser($credentials, [$credentials]);

        $app = $this->tester->createApp(
            $credentials,
            'get',
            '/blubber-threads/{id}/relationships/mentions',
            Mentions::class);

        $requestBuilder = $this->tester->createRequestBuilder($credentials);
        $requestBuilder->setUri('/blubber-threads/'.$thread->id.'/relationships/mentions')->fetch();

        $response = $this->tester->sendMockRequest($app, $requestBuilder->getRequest());
        $this->tester->assertTrue($response->isSuccessfulDocument([200]));

        $document = $response->document();
        $this->tester->assertTrue($document->isResourceCollectionDocument());

        $resources = $document->primaryResources();
        $this->tester->assertCount(count($thread->mentions), $resources);
    }

    public function testAddRelationship()
    {
        $credentials = $this->tester->getCredentialsForTestAutor();

        $thread = $this->createPrivateBlubberThreadForUser($credentials, [$credentials]);
        $this->tester->assertCount(1, $thread->mentions);

        $app = $this->tester->createApp(
            $credentials,
            'post',
            '/blubber-threads/{id}/relationships/mentions',
            Mentions::class);

        $requestBuilder = $this->tester->createRequestBuilder($credentials);
        $requestBuilder->setUri('/blubber-threads/'.$thread->id.'/relationships/mentions')
                       ->create()
                       ->setJsonApiBody($this->prepareValidBody([$this->tester->getCredentialsForTestDozent()]));

        $response = $this->tester->sendMockRequest($app, $requestBuilder->getRequest());

        $this->tester->assertSame(204, $response->getStatusCode());
        $this->tester->assertCount(2, \BlubberThread::find($thread->id)->mentions);
    }

    public function testRemoveRelationship()
    {
        $credentials1 = $this->tester->getCredentialsForTestAutor();
        $credentials2 = $this->tester->getCredentialsForTestDozent();
        $credentials3 = $this->tester->getCredentialsForTestAdmin();

        $thread = $this->createPrivateBlubberThreadForUser($credentials1, [$credentials1, $credentials2, $credentials3]);
        $this->tester->assertCount(3, $thread->mentions);

        $app = $this->tester->createApp(
            $credentials1,
            'delete',
            '/blubber-threads/{id}/relationships/mentions',
            Mentions::class);

        $requestBuilder = $this->tester->createRequestBuilder($credentials1);
        $requestBuilder->setUri('/blubber-threads/'.$thread->id.'/relationships/mentions')
                       ->delete()
                       ->setJsonApiBody($this->prepareValidBody([$credentials1]));

        $response = $this->tester->sendMockRequest($app, $requestBuilder->getRequest());

        $this->tester->assertSame(204, $response->getStatusCode());
        $this->tester->assertCount(2, \BlubberThread::find($thread->id)->mentions);
    }


    private function prepareValidBody(array $users)
    {
        return [
            'data' => array_map(
                function ($user) {
                    return [
                        'type' => UsersSchema::TYPE,
                        'id' => $user['id'],
                    ];
                },
                $users
            ),
        ];
    }
}
