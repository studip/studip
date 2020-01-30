<?php

require_once 'BlubberTestHelper.php';

class BlubberCommentsShowTest extends \Codeception\Test\Unit
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
    public function testShowComment()
    {
        // given
        $credentials = $this->tester->getCredentialsForTestAutor();
        $thread = $this->createPublicBlubberThreadForUser($credentials, 'Who knows Daskylos?');

        // Workaround old-style Stud.IP-API using $GLOBALS['user']
        $oldUser = $GLOBALS['user'];
        $GLOBALS['user'] = new \Seminar_User(\User::find($credentials['id']));

        $comment = $this->createBlubberComment($credentials, $thread, 'Autolykos knows him.');

        // Workaround old-style Stud.IP-API using $GLOBALS['user']
        $GLOBALS['user'] = $oldUser;

        $response = $this->fetchComment($credentials, $comment);

        $this->tester->assertTrue($response->isSuccessfulDocument([200]));

        $document = $response->document();
        $this->tester->assertTrue($document->isSingleResourceDocument());

        $resourceObject = $document->primaryResource();

        $this->tester->assertSame($comment->content, $resourceObject->attribute('content'));
    }
}
