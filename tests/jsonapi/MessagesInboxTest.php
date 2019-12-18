<?php


use JsonApi\Routes\Messages\InboxShow;

class MessagesInboxTest extends \Codeception\Test\Unit
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

    public function testShouldShowInitiallyEmptyInbox()
    {
        $credentials = $this->tester->getCredentialsForTestAutor();
        $response = $this->getInbox($credentials);

        $this->tester->assertTrue($response->isSuccessfulDocument([200]));
        $this->tester->assertEmpty($response->document()->primaryResources());
    }

    public function testShouldShowNonEmptyInboxAfterMessage()
    {
        $credentials = $this->tester->getCredentialsForTestAutor();

        \Message::send($credentials['id'], [$credentials['username']], 'empty subject', 'empty message');
        $response = $this->getInbox($credentials);

        $this->tester->assertTrue($response->isSuccessfulDocument([200]));
        $this->tester->assertCount(1, $response->document()->primaryResources());
    }

    // **** helper functions ****
    private function getInbox($credentials)
    {
        $app = $this->tester->createApp($credentials, 'get', '/users/{id}/inbox', InboxShow::class);

        return $this->tester->sendMockRequest(
            $app,
            $this->tester->createRequestBuilder($credentials)
            ->setUri('/users/'.($credentials['id']).'/inbox')
            ->fetch()
            ->getRequest()
        );
    }
}
