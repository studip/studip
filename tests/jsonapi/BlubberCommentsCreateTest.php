<?php

use JsonApi\Schemas\BlubberComment as Schema;
use JsonApi\Routes\Blubber\CommentsCreate;

require_once 'BlubberTestHelper.php';

class BlubberCommentsCreateTest extends \Codeception\Test\Unit
{
    use BlubberTestHelper;

    /**
     * @var \UnitTester
     */
    protected $tester;

    protected $oldUser;

    protected function _before()
    {
        \DBManager::getInstance()->setConnection('studip', $this->getModule('\\Helper\\StudipDb')->dbh);
    }

    protected function _after()
    {
        // Workaround old-style Stud.IP-API using $GLOBALS['user']
        $GLOBALS['user'] = $this->oldUser;
    }

    // tests
    public function testCreateComment()
    {
        // given
        $credentials = $this->tester->getCredentialsForTestAutor();
        $num = \BlubberComment::countBySQL('1');
        $thread = $this->createPublicBlubberThreadForUser($credentials, 'Who knows Daskylos?');

        $content = 'Augias tried it too.';
        $response = $this->createBlubberCommentJSONAPI($credentials, $thread, $content);
        $this->tester->assertTrue($response->isSuccessfulDocument([201]));

        $document = $response->document();
        $this->tester->assertTrue($document->isSingleResourceDocument());

        $this->tester->assertEquals($num + 1, \BlubberComment::countBySQL('1'));

        $resourceObject = $document->primaryResource();
        $this->tester->assertTrue(is_string($resourceObject->id()));
        $this->tester->assertSame(SCHEMA::TYPE, $resourceObject->type());

        $this->tester->assertSame($content, $resourceObject->attribute('content'));
    }

    private function createBlubberCommentJSONAPI(array $credentials, \BlubberThread $thread, $content)
    {
        $body = [
            'data' => [
                'type' => Schema::TYPE,
                'attributes' => [
                    'content' => $content
                ]
            ]
        ];

        $app = $this->tester->createApp($credentials, 'post', '/blubber-threads/{id}/comments', CommentsCreate::class);

        $requestBuilder = $this->tester->createRequestBuilder($credentials);
        $requestBuilder
            ->setUri('/blubber-threads/' . $thread->id . '/comments')
            ->create()
            ->setJsonApiBody($body);

        return $this->tester->sendMockRequest($app, $requestBuilder->getRequest());
    }
}
