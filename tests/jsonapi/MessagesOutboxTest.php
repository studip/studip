<?php


use JsonApi\Routes\Messages\OutboxShow;

class MessagesOutboxTest extends \Codeception\Test\Unit
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

    public function testShouldShowInitiallyEmptyOutbox()
    {
        $credentials = $this->tester->getCredentialsForTestAutor();
        $response = $this->getOutbox($credentials);

        $this->tester->assertTrue($response->isSuccessfulDocument([200]));
        $this->tester->assertEmpty($response->document()->primaryResources());
    }

    public function testShouldShowNonEmptyOutboxAfterMessage()
    {
        $credentials = $this->tester->getCredentialsForTestAutor();

        $this->sendMessage($credentials);
        $response = $this->getOutbox($credentials);

        $this->tester->assertTrue($response->isSuccessfulDocument([200]));
        $this->tester->assertCount(1, $response->document()->primaryResources());
    }

    // **** helper functions ****
    private function getOutbox($credentials)
    {
        $app = $this->tester->createApp($credentials, 'get', '/users/{id}/outbox', OutboxShow::class);

        return $this->tester->sendMockRequest(
            $app,
            $this->tester->createRequestBuilder($credentials)
            ->setUri('/users/'.($credentials['id']).'/outbox')
            ->fetch()
            ->getRequest()
        );
    }

    private function sendMessage(array $credentials)
    {
        // EVIL HACK
        $oldUser = $GLOBALS['user'];
        $GLOBALS['user'] = \User::find($credentials['id']);

        $message = \Message::send($credentials['id'], [$credentials['username']], 'empty subject', 'empty message');

        // EVIL HACK
        $GLOBALS['user'] = $oldUser;

        return $message;
    }
}
