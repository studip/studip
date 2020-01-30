<?php

require_once 'BlubberTestHelper.php';

class BlubberThreadsShowTest extends \Codeception\Test\Unit
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
    public function testShowPublicThread()
    {
        // given
        $credentials = $this->tester->getCredentialsForTestAutor();
        $thread = $this->createPublicBlubberThreadForUser($credentials, 'Who knows Daskylos?');

        $response = $this->fetchThread($credentials, $thread);

        $this->tester->assertTrue($response->isSuccessfulDocument([200]));

        $document = $response->document();
        $this->tester->assertTrue($document->isSingleResourceDocument());

        $resourceObject = $document->primaryResource();

        $this->tester->assertSame($thread->context_type, $resourceObject->attribute('context-type'));
        $this->tester->assertSame($thread->content, $resourceObject->attribute('content'));

        // check predicates
        $this->tester->assertSame($thread->isCommentable($credentials['id']), $resourceObject->attribute('is-commentable'));
        $this->tester->assertSame($thread->isReadable($credentials['id']), $resourceObject->attribute('is-readable'));
        $this->tester->assertSame($thread->isWritable($credentials['id']), $resourceObject->attribute('is-writable'));
    }

    public function testShowCourseThread()
    {
        // given
        $credentials = $this->tester->getCredentialsForTestAutor();
        $courseId = 'a07535cf2f8a72df33c12ddfa4b53dde';
        $thread = $this->createCourseBlubberThreadForUser($credentials, $courseId, 'I have seen Eribotes!');

        $response = $this->fetchThread($credentials, $thread);

        $this->tester->assertTrue($response->isSuccessfulDocument([200]));

        $document = $response->document();
        $this->tester->assertTrue($document->isSingleResourceDocument());

        $resourceObject = $document->primaryResource();

        $this->tester->assertSame($thread->context_type, $resourceObject->attribute('context-type'));
        $this->tester->assertSame($thread->content, $resourceObject->attribute('content'));

        // check predicates
        $this->tester->assertSame($thread->isCommentable($credentials['id']), $resourceObject->attribute('is-commentable'));
        $this->tester->assertSame($thread->isReadable($credentials['id']), $resourceObject->attribute('is-readable'));
        $this->tester->assertSame($thread->isWritable($credentials['id']), $resourceObject->attribute('is-writable'));
    }
}
