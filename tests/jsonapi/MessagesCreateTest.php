<?php


use JsonApi\Routes\Messages\MessageCreate;
use JsonApi\Schemas\Message;
use JsonApi\Schemas\User as UserSchema;

class MessagesCreateTest extends \Codeception\Test\Unit
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

    public function testShouldCreatePublicPosting()
    {
        $credentials = $this->tester->getCredentialsForTestDozent();
        $recipients = [
            $this->tester->getCredentialsForTestAutor(),
            $this->tester->getCredentialsForRoot(),
        ];
        $subject = 'Unterbrechen Sie, was immer Sie gerade tun. Dies mussen Sie sich ansehen.';
        $message = 'Euryalos hat einen Business-Vorschlag fur Sie.';
        $tags = ['foo', 'bar', 'baz'];

        $response = $this->createMessage($credentials, $recipients, $subject, $message, $tags);

        $this->tester->assertTrue($response->isSuccessfulDocument([201]));

        $document = $response->document();
        $this->tester->assertTrue($document->isSingleResourceDocument());

        $resource = $document->primaryResource();
        $this->tester->assertNotEmpty($resource->id());
        $this->tester->assertSame(Message::TYPE, $resource->type());

        $this->tester->assertSame($subject, $resource->attribute('subject'));
        $this->tester->assertSame($message, $resource->attribute('message'));

        $this->tester->assertTrue($resource->hasRelationship('recipients'));

        $recipientsRel = $resource->relationship('recipients');
        $this->tester->assertTrue($recipientsRel->isToManyRelationship());

        $this->tester->assertSame(count($recipients), count($recipientsRel->resourceLinks()));
    }

    // **** helper functions ****
    private function createMessage($sender, $recipients, $subject, $content, array $tags)
    {
        $requestBuilder = $this->tester->createRequestBuilder($sender);
        $requestBuilder
            ->setUri('/messages')
            ->setJsonApiBody($this->prepareValidBody($recipients, $subject, $content, $tags))
            ->create();

        $app = $this->tester->createApp($sender, 'post', '/messages', MessageCreate::class);

        return $this->tester->sendMockRequest($app, $requestBuilder->getRequest());
    }

    private function prepareValidBody(array $recipients, $subject, $message, $tags)
    {
        $recipientsData = array_reduce(
            $recipients,
            function ($memo, $recipient) {
                $memo[] = [
                    'type' => UserSchema::TYPE,
                    'id' => $recipient['id'],
                ];

                return $memo;
            },
            []
        );

        $json = [
            'data' => [
                'type' => Message::TYPE,
                'attributes' => [
                    'subject' => $subject,
                    'message' => $message,
                    'tags' => $tags,
                ],
                'relationships' => [
                    'recipients' => [
                        'data' => $recipientsData,
                    ],
                ],
            ],
        ];

        return $json;
    }
}
