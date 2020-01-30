<?php

use JsonApi\Schemas\BlubberComment as Schema;
use JsonApi\Routes\Blubber\CommentsUpdate;

require_once 'BlubberTestHelper.php';

class BlubberCommentsUpdateTest extends \Codeception\Test\Unit
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
    public function testUpdateOwnComment()
    {
        // given
        $credentials = $this->tester->getCredentialsForTestAutor();
        $thread = $this->createPublicBlubberThreadForUser($credentials, 'Who knows Daskylos?');

        $num = \BlubberComment::countBySQL('1');
        $comment = $this->createBlubberComment($credentials, $thread, 'Autolykos knows him.');
        $this->tester->assertEquals($num + 1, \BlubberComment::countBySQL('1'));

        $content = 'Who knows Erginos?';
        $response = $this->updateBlubberCommentJSONAPI($credentials, $comment, $content);
        $this->tester->assertSame(204, $response->getStatusCode());
    }

    public function testUpdateOtherCommentFail()
    {
        // given
        $credentialsAutor = $this->tester->getCredentialsForTestAutor();
        $credentialsDozent = $this->tester->getCredentialsForTestDozent();
        $thread = $this->createPublicBlubberThreadForUser($credentialsDozent, 'Who knows Daskylos?');

        $num = \BlubberComment::countBySQL('1');
        $comment = $this->createBlubberComment($credentialsAutor, $thread, 'Autolykos knows him.');
        $this->tester->assertEquals($num + 1, \BlubberComment::countBySQL('1'));

        $this->tester->expectThrowable(\JsonApi\Errors\AuthorizationFailedException::class, function () use (
            $credentialsDozent,
            $comment
        ) {
            $content = 'Who knows Erginos?';
            $this->updateBlubberCommentJSONAPI($credentialsDozent, $comment, $content);
        });
    }

    public function testUpdateOtherCommentSuccess()
    {
        // given
        $credentialsAutor = $this->tester->getCredentialsForTestAutor();
        $credentialsRoot = $this->tester->getCredentialsForRoot();
        $thread = $this->createPublicBlubberThreadForUser($credentialsRoot, 'Who knows Daskylos?');

        $num = \BlubberComment::countBySQL('1');
        $comment = $this->createBlubberComment($credentialsAutor, $thread, 'Autolykos knows him.');
        $this->tester->assertEquals($num + 1, \BlubberComment::countBySQL('1'));

        $content = 'Who knows Erginos?';
        $response = $this->updateBlubberCommentJSONAPI($credentialsRoot, $comment, $content);
        $this->tester->assertSame(204, $response->getStatusCode());
    }

    private function updateBlubberCommentJSONAPI(array $credentials, \BlubberComment $comment, string $content)
    {
        $body = [
            'data' => [
                'id' => $comment->id,
                'type' => Schema::TYPE,
                'attributes' => [
                    'content' => $content
                ]
            ]
        ];

        $app = $this->tester->createApp($credentials, 'patch', '/blubber-comments/{id}', CommentsUpdate::class);

        $requestBuilder = $this->tester->createRequestBuilder($credentials);
        $requestBuilder
            ->setUri('/blubber-comments/' . $comment->id)
            ->setJsonApiBody($body)
            ->update();

        return $this->tester->sendMockRequest($app, $requestBuilder->getRequest());
    }
}
