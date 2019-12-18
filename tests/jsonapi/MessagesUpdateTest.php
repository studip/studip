<?php

use JsonApi\Routes\Messages\MessageShow;
use JsonApi\Routes\Messages\MessageUpdate;

class MessagesUpdateTest extends \Codeception\Test\Unit
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
    public function testMarkMessageAsRead()
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
        $this->tester->assertFalse($resourceObject->attribute('is-read'));

        $response2 = $this->markMessageAsRead($credentials, $message);
        $this->tester->assertTrue($response2->isSuccessfulDocument([200]));
        $this->tester->assertTrue($response2->document()->primaryResource()->attribute('is-read'));
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

    private function markMessageAsRead(array $credentials, \Message $message)
    {
        $json = [
            'data' => [
                'type' => 'messages',
                'id' => $message->id,
                'attributes' => [
                    'is-read' => true,
                ],
            ],
        ];

        return $this->tester->sendMockRequest(
            $this->tester->createApp($credentials, 'patch', '/messages/{id}', MessageUpdate::class),
            $this->tester->createRequestBuilder($credentials)
            ->setUri('/messages/'.$message->id)
            ->setJsonApiBody($json)
            ->update()
            ->getRequest()
        );
    }
}
