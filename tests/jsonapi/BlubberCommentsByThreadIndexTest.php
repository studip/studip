<?php

require_once 'BlubberTestHelper.php';

class BlubberCommentsByThreadIndexTest extends \Codeception\Test\Unit
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

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function testShowThread()
    {
        // given
        $credentials = $this->tester->getCredentialsForTestAutor();
        $thread = $this->createPublicBlubberThreadForUser($credentials, 'Who knows Daskylos?');

        // Workaround old-style Stud.IP-API using $GLOBALS['user']
        $oldUser = $GLOBALS['user'];
        $GLOBALS['user'] = new \Seminar_User(\User::find($credentials['id']));

        $this->createBlubberComment($credentials, $thread, 'Autolykos knows him.');
        $this->createBlubberComment($credentials, $thread, 'Butes knows him too.');

        // Workaround old-style Stud.IP-API using $GLOBALS['user']
        $GLOBALS['user'] = $oldUser;

        $response = $this->fetchComments($credentials, $thread);

        $this->tester->assertTrue($response->isSuccessfulDocument([200]));

        $document = $response->document();
        $this->tester->assertTrue($document->isResourceCollectionDocument());

        $resources = $document->primaryResources();
        $this->tester->assertCount(2, $resources);
    }
}
