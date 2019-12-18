<?php


use JsonApi\Routes\Messages\MessageDelete;

class MessagesDeleteTest extends \Codeception\Test\Unit
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
    public function testDeleteMessage()
    {
        $credentials = $this->tester->getCredentialsForTestAutor();
        $recipients = [$this->tester->getCredentialsForTestDozent()['username']];

        $this->tester->assertSame(0, $this->countMessages($credentials['id'], false));
        $message = \Message::send($credentials['id'], $recipients, 'empty subject', 'empty message');
        $this->tester->assertSame(1, $this->countMessages($credentials['id'], false));

        $response = $this->deleteMessage($credentials, $message);
        $this->tester->assertSame(204, $response->getStatusCode());

        $this->tester->assertSame(0, $this->countMessages($credentials['id'], false));
    }

    //helpers
    private function countMessages($userId, $received)
    {
        $query = 'SELECT COUNT(*)
                  FROM message_user
                  WHERE snd_rec = ? AND user_id = ? AND deleted = 0
                  ORDER BY mkdate DESC';

        $statement = \DBManager::get()->prepare($query);
        $statement->execute([$received ? 'rec' : 'snd', $userId]);

        return (int) $statement->fetch(\PDO::FETCH_COLUMN, 0);
    }

    private function deleteMessage($credentials, \Message $message)
    {
        return $this->tester->sendMockRequest(
            $this->tester->createApp($credentials, 'delete', '/messages/{id}', MessageDelete::class),
            $this->tester->createRequestBuilder($credentials)
            ->setUri('/messages/'.($message->id))
            ->delete()
            ->getRequest()
        );
    }
}
