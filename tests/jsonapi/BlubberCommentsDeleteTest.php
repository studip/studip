<?php

use JsonApi\Schemas\BlubberComment as Schema;
use JsonApi\Routes\Blubber\CommentsDelete;

require_once 'BlubberTestHelper.php';

class BlubberCommentsDeleteTest extends \Codeception\Test\Unit
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
    public function testCreateComment()
    {
        // given
        $credentials1 = $this->tester->getCredentialsForTestAutor();
        $credentials2 = $this->tester->getCredentialsForRoot();
        $thread = $this->createPublicBlubberThreadForUser($credentials2, 'Who knows Daskylos?');

        // Workaround old-style Stud.IP-API using $GLOBALS['user']
        $oldUser = $GLOBALS['user'];
        $GLOBALS['user'] = new \Seminar_User(\User::find($credentials1['id']));

        $num = \BlubberComment::countBySQL('1');
        $comment = $this->createBlubberComment($credentials1, $thread, 'Autolykos knows him.');
        $this->tester->assertEquals($num + 1, \BlubberComment::countBySQL('1'));

        // Workaround old-style Stud.IP-API using $GLOBALS['user']
        $GLOBALS['user'] = $oldUser;

        $response = $this->deleteBlubberCommentJSONAPI($credentials2, $comment);
        $this->tester->assertSame(204, $response->getStatusCode());

        $this->tester->assertEquals($num, \BlubberComment::countBySQL('1'));
    }

    private function deleteBlubberCommentJSONAPI(array $credentials, \BlubberComment $comment)
    {
        $app = $this->tester->createApp($credentials, 'delete', '/blubber-comments/{id}', CommentsDelete::class);

        $requestBuilder = $this->tester->createRequestBuilder($credentials);
        $requestBuilder
            ->setUri('/blubber-comments/' . $comment->id)
            ->delete();

        return $this->tester->sendMockRequest($app, $requestBuilder->getRequest());
    }
}
