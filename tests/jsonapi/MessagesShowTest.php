<?php


use JsonApi\Errors\RecordNotFoundException;
use JsonApi\Routes\Messages\MessageShow;

class MessagesShowTest extends \Codeception\Test\Unit
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
    public function testMessageNotFound()
    {
        $this->tester->expectThrowable(RecordNotFoundException::class, function () {
            $credentials = $this->tester->getCredentialsForTestAutor();
            $response = $this->fetchMessage($credentials, new \Message(md5('eurydamas')));
        });
    }

    public function testShowMessage()
    {
        $credentials = $this->tester->getCredentialsForTestAutor();
        $recipients = [$this->tester->getCredentialsForTestDozent()['username']];
        $message = \Message::send($credentials['id'], $recipients, 'empty subject', 'empty message');

        $response = $this->fetchMessage($credentials, $message);

        $this->tester->assertTrue($response->isSuccessfulDocument([200]));
        $document = $response->document();
        $this->tester->assertTrue($document->isSingleResourceDocument());
        $resourceObject = $document->primaryResource();
        $this->tester->assertSame($message->subject, $resourceObject->attribute('subject'));
    }

    // helpers
    private function fetchMessage(array $credentials, \Message $message)
    {
        return $this->tester->sendMockRequest(
            $this->tester->createApp($credentials, 'get', '/messages/{id}', MessageShow::class),
            $this->tester
                 ->createRequestBuilder($credentials)
                 ->setUri('/messages/'.$message->id)
                 ->fetch()
                 ->getRequest()
        );
    }
}
